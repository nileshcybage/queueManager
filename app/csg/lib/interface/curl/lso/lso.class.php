<?php

/**
 * This class is the Soap Webservice Server class for LSO.
 * @author Gaurav Gupta
 * @created  01-Mar-2021 16:10 PM PST
 * @changed
 * @reviewed by
 * @version 1.0
 * @package CSG WebService for LSO
 */
include_once(CSG_LIB_INTERFACE . 'curl/curl.class.php');

class clsLSO extends clsCurlClient
{
    /**
     * Constructor of the class
     */
    public function __construct($wsdlPath = '', $className = '')
    {
        // Second parameter true as we want to bypass the SSL Cerificate errors
        parent::__construct($wsdlPath, true);
    }

    /**
    * Function to fetch the trackign request response from carrier
    * @return $res - Response
    * @author Gaurav Gupta
    * @Date: 04-Apr-2021 1.00 PM IST
    */
    public function getTrackingHistory($trackId)
    {
        $requestXML = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="https://services.lso.com/TrackingService/v1_6">
                    <soapenv:Header/>
                    <soapenv:Body>
                    <v1:TrackSinglePackage>
                    <!--Optional:-->
                    <v1:request>
                    <!--Optional:-->
                    <v1:AuthenticationInfo>
                    <!--Optional:-->
                    <v1:Username>' . CFG_LSO_USERNAME . '</v1:Username>
                    <!--Optional:-->
                    <v1:Password>' . CFG_LSO_PASSWORD . '</v1:Password>
                    </v1:AuthenticationInfo>
                    <!--Optional:-->
                    <v1:Customer>
                    <v1:AccountNumber>' . CFG_LSO_ACCOUNT_NUMBER . '</v1:AccountNumber>
                    </v1:Customer>
                    <!--Optional:-->
                    <v1:AirbillNumber>' . $trackId . '</v1:AirbillNumber>
                    </v1:request>
                    </v1:TrackSinglePackage>
                    </soapenv:Body>
                    </soapenv:Envelope>';

        /* CURL service URL. */
        $ch = curl_init(CFG_LSO_TRACKING_URL);

        /* Setting HEADER, FOLLOWLOCATION, POST etc as a parameter into the Curl  */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "SOAPAction: https://services.lso.com/TrackingService/v1_6/TrackSinglePackage",
        ));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        /* Setting the request XML as a parameter into the Curl  */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXML);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);

        /* $result : This variable contained the result XML. Here curl is being executed */
        $responseXML = curl_exec($ch);
        curl_close($ch);

        $responseXML = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $responseXML);
        //response sanity check to avoid logging error
        if(preg_match("/504/", $responseXML)){
            echo 'Gateway timeout ...';
            return false;
        }
        $xml = new SimpleXMLElement($responseXML);
        $body = $xml->xpath('//soapBody')[0];
        $response = json_decode(json_encode((array) $body), true);

        if (isset($response['soapFault'])) {
            $eResArr = array();
            $eResArr = $response['soapFault']['detail']['webServiceException']['code'];
            $eResDetail = $response['soapFault']['detail']['webServiceException']['action'];
            return new SoapFault($eResArr, "Client", "getTrackingHistory", $eResDetail);
        } else {
            $processed_Response = $response['TrackSinglePackageResponse']['TrackSinglePackageResult']['Package'];
            if (empty($processed_Response['Steps'])) {
                echo 'NO Steps found';
                return true;
            } else {
                $res = $this->processResponse($processed_Response);
                return array($res);
            }
        }
    }
    /**
    * Function to process response in well formatted array to process for shipment updates
    * @return $arrActivityResult
    * @author Gaurav Gupta
    * @Date: 04-Apr-2021 1.00 PM IST
    */

    public function processResponse($response)
    {
        $newres = array();
        $arrActivityResult = array();
        $arrEvents = array();

        if (is_array($response) && isset($response)) {
            $arrActivities = $response['Steps'];
            $eventCity = '';
            $deliveredDate = '';
            $deliveredTime = '';
            $pickupDate = '';
            $pickupTime = '';

            if (is_array($arrActivities) && !empty($arrActivities['PackageTrackingStep'][0])) {
                $arrActivities = $arrActivities['PackageTrackingStep'];
            }

            foreach ($arrActivities as $key=>$activity) {
                if (isset($activity['Status']) &&  $activity['Status']== 'OnTruck' || $activity['Status'] == 'Delivered') {
                    $activityDateTime = convertUTCtoEST($activity['TrackingDate']);
                    $deliveredDate = date('Y-m-d', strtotime($activityDateTime));
                    $deliveredTime = date('h:i a', strtotime($activityDateTime));
                    $arrActivityResult['TrackSummary']['ActualDeliveryDate'] = $deliveredDate . $deliveredTime;
                }

                if ($activity['Status'] == 'PickedUp') {
                    $pickupDateTime = convertUTCtoEST($activity['TrackingDate']);
                    $pickupDate = date('Y-m-d', strtotime($pickupDateTime));
                    $pickupTime = date('h:i a', strtotime($pickupDateTime));
                }

                $arrEvents[$activity['Status']] = $activity;
                $arrActivityResult['TrackSummary']['Events'] = $arrEvents;
            }

            $arrActivityResult['TrackSummary']['Events']['ACTUAL_PICKUP']= $pickupDateTime;
            $arrActivityResult['TrackSummary']['EventSummaryDesc'] = $response['StatusDescription'];
            $arrActivityResult['TrackSummary']['EventCity'] = $response['Package']['FromAddress']['City'];
            $arrActivityResult['TrackSummary']['EventState'] = $response['Package']['FromAddress']['State'];
            $arrActivityResult['TrackSummary']['EventZIPCode'] = $response['Package']['FromAddress']['ZipCode'];
            $arrActivityResult['TrackSummary']['EventCountry'] = $response['Package']['FromAddress']['Country'];
            $arrActivityResult['TrackSummary']['EventResidential'] = '';
            $arrActivityResult['TrackSummary']['EventDate'] = $pickupDate;
            $arrActivityResult['TrackSummary']['EventTime'] = $pickupTime;
            $arrActivityResult['TrackSummary']['CarrierCode']= 'LSO';
            $arrActivityResult['TrackSummary']['ServiceType']= $response['Package']['ServiceType'];
            $arrActivityResult['TrackSummary']['EventSummaryDateTime'] = convertUTCtoEST($response['LastDataRetrievalDate']);
        }
        return $arrActivityResult;
    }
}

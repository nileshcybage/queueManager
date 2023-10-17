<?php

/**
 * This class is the REST Web-service Server class to handle all web-services.
 * @author Akshay Jadhav & Jana Davangave
 * @created  31-Jul-2020 16:10 PM PST
 * @changed
 * @reviewed by
 * @version 1.0
 * @package CSG WebService
 */
include_once(CSG_LIB_INTERFACE . 'curl/curl.class.php');
/*
 * Including files for udpating the GLS TOKEN VALUE to configuration
 */
include_once(CFG_DATA . 'dataconfigurations.class.php');
include_once(CFG_LIB_MODULES . 'configuration/configurationsmaster.class.php');

class clsGLS extends clsCurlClient
{
    /**
     * Constructor of the class
     */
    private $arrStatus;

    public function __construct($wsdlPath = '', $className = '')
    {
        // Second parameter true as we want to bypass the SSL Cerificate errors
        parent::__construct($wsdlPath, true);
        $this->arrStatus = array('I' => 'In Transit', 'D' => 'Delivered', 'X' => 'Exception', 'P' => 'Pickup', 'M' => 'Manifest Pickup');
    }

    /**
     * A function to generate GLS API Access Token & Update the same in config related table.
     * @return Token Value.
     * @author Akshay Jadhav
     * @Date: 31-Jul-2020 1.00 PM IST
     */
    public function generateAndUpdateNewToken()
    {
        $strUrl = CFG_GLS_TOKEN_URL;
        $arrParams['Header'] = array(
            "cache-control: no-cache",
            "content-type: application/json",
            "Username: " . CFG_GLS_USERNAME,
            "Password: " . CFG_GLS_PASSWORD,
        );
        $arrParams['HeaderFunction'] = 1;
        $restResponse = $this->callServiceRest($strUrl, $arrParams);
        $tokenData = array();
        $response = $restResponse['arrayResponse'];
        $http_status = $restResponse['http_status'];
        if ($http_status >= 300) {
            $errorResponse = $response; //$errorResponse->StatusCode." : ".
            $msg = (isset($errorResponse->Message)) ? $errorResponse->Message : $errorResponse->StatusDescription . " - " . $errorResponse->ErrorDetail[0]->ErrorDescription;
            $tokenData['Error'] = $msg;
        } elseif ($http_status >= 200 && $http_status <= 299) {
            $headers = $restResponse['headers'];
            $tokenData['Token'] = $headers['token'][0];
            $tokenData['IsUpdated'] = $this->updateConfigurationToken($tokenData);
        }
    }

    /**
     * A function to update the GLS API Access Token in configurations related table.
     * @return Token Value.
     * @author Akshay Jadhav
     * @Date: 04-Aug-2020 2.00 PM IST
     */
    public function updateConfigurationToken($tokenData)
    {
        $objConfiguration = new clsConfigurationsMaster();
        $arrEditConfData = array('configuration_value' => $tokenData['Token']);
        $arrEditWhereData = array('configuration_key' => 'USAP_GSO_REST_TOKEN');
        $intRowsEdited = $objConfiguration->mObjConfiguration->editConfiguration($arrEditWhereData, $arrEditConfData);
        return ($intRowsEdited > 0) ? true : false;
    }

    /**
     * A function to create json request and generate response for GLS TrackShipment.
     * @return Std Response.
     * @author Jana Davangave
     * @Date: 31-Jul-2020 1.00 PM IST
     */
    public function getTrackingHistory($trackId)
    {
        if (!isset($trackId)) {
            return false;
        }
        $trackUrl = CFG_GLS_TRACKING_URL . "?AccountNumber=" . CFG_GLS_ACCOUNT_NUMBER . "&TrackingNumber=" . $trackId;

        $strJSONContent['Header'] = array(
            "cache-control: no-cache",
            "content-type: application/json",
            "token:" . USAP_GSO_REST_TOKEN,
        );
        $restArray = $this->callServiceRest($trackUrl, $strJSONContent); //Call method from clsCurlClient.
        $response = $restArray['arrayResponse'];

        if (401 === (int) $restArray['http_status']) {
            $strResponse = $response->ErrorDetail[0]->ErrorDescription;
            $restArray['detail'] = $strResponse;
            $checkResponse = "Token has been expired";
            if (strpos($strResponse, $checkResponse) !== false) {
                $tokenData = $this->generateAndUpdateNewToken();
                if (isset($tokenData['Token'])) {
                    $newToken = $tokenData['Token'];
                    $strJSONContent['Header'] = array(
                        "cache-control: no-cache",
                        "content-type: application/json",
                        "token:" . $newToken,
                    );
                    $restArray = $this->callServiceRest($trackUrl, $strJSONContent); //Call method from clsCurlClient.
                    $response = $restArray['arrayResponse'];
                } else {
                    $restArray['detail'] = $tokenData['Error'];
                    return $restArray;
                }
            } else {
                echo "[GLS : $trackId]: 401 - " . $strResponse;
                return $restArray;
            }
        }

        if ($restArray['http_status'] >= 200 && $restArray['http_status'] <= 299) {
            $res = $this->processResponse($response);
            return array($res);
        } else {
            //400-Bad request and 500-Internal server ,401- Unauthourized
            $strResponse = $response->ErrorDetail[0]->ErrorDescription;
            echo "[GLS : $trackId]:" . $restArray['http_status'] . " - " . $strResponse;
            $restArray['detail'] = $strResponse;
            if (400 === (int) $restArray['http_status']) {
                $arrResponseToStopShipper = array("No Shipment found.", "Tracking Number length cannot be greater than 20 characters.");
                if (in_array($strResponse, $arrResponseToStopShipper)) {
                    $restArray['stopCode'] = "Shipment not found or Tracking Number length not valid";
                }
            }
            return $restArray;
        }
    }

    /**
     * A function to Process response for GLS TrackShipment in Std format.
     * @return Std Response.
     * @author Jana Davangave
     * @Date: 3-Aug-2020 1.30 PM IST
     */
    public function processResponse($response)
    {
        $newres = array();
        $arrActivityResult = array();
        $arrEvents = array();
        if (is_object($response) && isset($response)) {
            $count = count($response->ShipmentInfo);
            $res = $response->ShipmentInfo [$count-1];
            $resDelivery = $res->Delivery;
            $arrLastActivity = $res->TransitNotes;
            $j = 0;
            foreach ($arrLastActivity as $activity) {
                $eventDate = '';
                $eventTime = '';
                if (isset($activity->EventDate) && $activity->EventDate!='') {
                    $eventDate = date('F j, Y', strtotime($activity->EventDate));
                    $eventTime = date('h:i a', strtotime($activity->EventDate));
                }
                $arrLocation = explode(',', $activity->Location, 2);
                $newres['EventDate']    = $eventDate;
                $newres['EventTime']    = $eventTime;
                $newres['EventCity']    = trim($arrLocation[0]);
                $newres['EventState']   = trim($arrLocation[1]);
                $newres['EventZIPCode'] = '';
                $newres['EventCountry'] = $activity->Location;
                $newres['EventDesc']    = $activity->Comments;
                $arrActivityResult['TrackDetail'][$j] = $newres;
                $j++;

                $statusCodes[] = '';   //its like delivered code is 3
                $arrEvents[trim($activity->Comments)] = date('Y-m-d', strtotime($activity->EventDate))."T".date('H:i:s', strtotime($activity->EventDate));
                $arrActivityResult['TrackSummary']['Events'] = $arrEvents;
                $arrActivityResult['TrackSummary']['EventCity']   = $newres['EventCity'];
                $arrActivityResult['TrackSummary']['EventState']  = $newres['EventState'];
                $arrActivityResult['TrackSummary']['EventDate']  = $newres['EventDate'];
                $arrActivityResult['TrackSummary']['EventTime']  = $newres['EventTime'];
            }
            $arrActivityResult['TrackSummary']['EventSummaryDesc'] = strtolower(trim($resDelivery->TransitStatus));
            $pickupDate = '';
            $schDelDate = '';
            $schDelTime = '';
            if (isset($arrLastActivity[0]->EventDate) && $arrLastActivity[0]->EventDate != '') {
                $pickupDate = date('F j, Y', strtotime($arrLastActivity[0]->EventDate));
                $actualPickup = date('Y-m-d', strtotime($arrLastActivity[0]->EventDate));
            }
            if (isset($resDelivery->ScheduledDeliveryDate) && $resDelivery->ScheduledDeliveryDate != '') {
                $schDelDate = date('F j, Y', strtotime($resDelivery->ScheduledDeliveryDate));
            }
            if (isset($resDelivery->ScheduledDeliveryTime) && $resDelivery->ScheduledDeliveryTime != '') {
                $schDelTime = date('H:i:s', strtotime($resDelivery->ScheduledDeliveryTime));
            }
            if (isset($resDelivery->DeliveryDate) && !empty($resDelivery->DeliveryDate)) {
                $eventSummaryDateTime = date('Y-m-d', strtotime($resDelivery->DeliveryDate))." ".date('H:i:s', strtotime($resDelivery->DeliveryTime));
            }
            if (strtolower(trim($resDelivery->TransitStatus)) === 'delivered') {
                $arrActivityResult['TrackSummary']['ActualDeliveryDate'] = date('Y-m-d', strtotime($resDelivery->DeliveryDate));
                $arrActivityResult['TrackSummary']['ActualDeliveryTime'] = date('H:i:s', strtotime($resDelivery->DeliveryTime));
            }
            // Recent status will be the Summary Status.
            $arrActivityResult['TrackSummary']['Events']['ACTUAL_PICKUP']= $actualPickup;
            $arrActivityResult['TrackSummary']['EventZIPCode']          = $res->DeliveryZip;
            $arrActivityResult['TrackSummary']['EventCountry']          = '';
            $arrActivityResult['TrackSummary']['EventResidential']      = '';
            $arrActivityResult['TrackSummary']['EstimatedDeliveryDate'] = $schDelDate;
            $arrActivityResult['TrackSummary']['EstimatedDeliveryTime'] = $schDelTime;
            $arrActivityResult['TrackSummary']['CarrierCode']           = $res->ServiceCode;
            $arrActivityResult['TrackSummary']['ServiceType']           = $res->ServiceCode;
            $arrActivityResult['TrackSummary']['EventSummaryDateTime']  = $eventSummaryDateTime;
        }
        return $arrActivityResult;
    }
}

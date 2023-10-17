<?php
/**
 * This class is the Soap Webservice Server class to handle all webservices.
 * @author Samir Shelar
 * @created  15-Jun-2010 16:10 PM PST
 * @changed
 * @reviewed by
 * @version 1.0
 * @package CSG WebService
 */
include_once(CSG_LIB_INTERFACE.'webservice/client/client.class.php');
class clsFedex extends clsSoapClient
{
    /**
     * Constructor of the class
     */
    public function __construct()
    {
        parent::__construct(CFG_FEDEX_WSDL);
    }

    /**
     * Function to get history of Tracking
     * @param string $strTrackingNumber
     * @return array $arrResult
     * @author Sukhada Mahajan
     * @ Date:  16-JUNE-2010
     * @ Reviewed by: TBD
     */

    public function getTrackingHistory($strTrackingNumber)
    {

        /* ------------- Set the configuration values for FedEx service call --------------------------------------*/
        $arrRequest['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => CFG_FEDEX_KEY, 'Password' => CFG_FEDEX_PASSWORD));
        $arrRequest['ClientDetail'] = array('AccountNumber' => CFG_FEDEX_SHIPACCOUNT, 'MeterNumber' => CFG_FEDEX_METER);
        $arrRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request v4 using PHP ***');
        $arrRequest['Version'] = array('ServiceId' => 'trck', 'Major' => '4', 'Intermediate' => '0', 'Minor' => '0');
        $arrRequest['PackageIdentifier'] = array('Value' => $strTrackingNumber,
                                      'Type' => CFG_FEDEX_TYPE);

        /* ------------ Catch the soap fault  ------------ */
        try {
            $arrResponse = $this->callService('track', $arrRequest);//Call method from clsSOAPClient.
            //print_r($arrResponse->TrackDetails); exit;
            if ($arrResponse -> HighestSeverity != 'FAILURE' && $arrResponse -> HighestSeverity != 'ERROR') {
                $arrResult = $this->processResponse($arrResponse);
                return array($arrResult);
            } else {
                return new SoapFault($arrResponse->Notifications->Code, "Client", "getTrackingHistory", $arrResponse->Notifications->Message);
            }
        } catch (SoapFault $exception) {
            return new SoapFault($arrResponse->Notifications->Code, "Client", "getTrackingHistory", $arrResponse->Notifications->Message);
        }
    }

    /**
     * Function to process the response
     * @param array $arrResponse
     * @return array $arrUpdatedResult
     * @author Sukhada Mahajan
     * @ Date:  16-JUNE-2010
     * @ Reviewed by: TBD
     */

    public function processResponse($arrResponse)
    {
        $arrResult = $arrResponse->TrackDetails;

        /* ------ The FedEx response varies for some tracking numbers. So taking the response in array everytime --------*/
        if (!is_array($arrResult)) {
            $arrResult = array($arrResult);
        }

        $j= 0;
        if (is_array($arrResult) && isset($arrResult)) {
            foreach ($arrResult as $key=>$val) {
                $arrDestinationAddress = $arrResult[$key]->DestinationAddress;

                if ($arrDestinationAddress->City !="" || $arrDestinationAddress->StateOrProvinceCode !="" || $arrDestinationAddress->CountryCode !="" || $arrDestinationAddress->Residential !="") {
                    //LASTEST STSTUS WILL COME LAST

                    $arrUpdatedResult['TrackSummary']['EventCity'] = $arrDestinationAddress->City;
                    $arrUpdatedResult['TrackSummary']['EventState'] =$arrDestinationAddress->StateOrProvinceCode;
                    $arrUpdatedResult['TrackSummary']['EventZIPCode'] =$arrDestinationAddress->PostalCode;
                    $arrUpdatedResult['TrackSummary']['EventCountry'] =  $arrDestinationAddress->CountryCode;
                    $arrUpdatedResult['TrackSummary']['EventResidential'] =  $arrDestinationAddress->Residential;
                    $arrUpdatedResult['TrackSummary']['EventSummaryDesc'] = $arrResult[$key]->StatusDescription;
                    $arrEstimatedDeliveryTimestamp = explode("T", $arrResult[$key]->EstimatedDeliveryTimestamp);
                    if (count($arrEstimatedDeliveryTimestamp) >0) {
                        $EstimatedDeliverydatetime = date_create($arrEstimatedDeliveryTimestamp[0].''. $arrEstimatedDeliveryTimestamp[1]);
                        $arrUpdatedResult['TrackSummary']['EstimatedDeliveryDate'] = date_format($EstimatedDeliverydatetime, 'F j, Y');
                        $arrUpdatedResult['TrackSummary']['EstimatedDeliveryTime'] = date_format($EstimatedDeliverydatetime, 'h:i a');
                    }
                    $arrUpdatedResult['TrackSummary']['CarrierCode'] = $arrResult[$key]->CarrierCode;
                    $arrUpdatedResult['TrackSummary']['ServiceType'] = $arrResult[$key]->ServiceType;
                    $arrDateTime = explode("T", $arrResult[$key]->ShipTimestamp);

                    if (count($arrDateTime) >0) {
                        $datetime = date_create($arrDateTime[0].''. $arrDateTime[1]);
                        $arrUpdatedResult['TrackDetail'][$j]['EventDate'] = date_format($datetime, 'F j, Y');
                        $arrUpdatedResult['TrackDetail'][$j]['EventTime'] = date_format($datetime, 'h:i a');
                        $arrUpdatedResult['TrackSummary']['EventDate'] = date_format($datetime, 'F j, Y');
                        $arrUpdatedResult['TrackSummary']['EventTime'] = date_format($datetime, 'h:i a');
                    }

                    $arrUpdatedResult['TrackDetail'][$j]['EventCity'] = $arrResult[$key]->Events->Address->City;
                    $arrUpdatedResult['TrackDetail'][$j]['EventState'] = $arrResult[$key]->Events->Address->StateOrProvinceCode;
                    $arrUpdatedResult['TrackDetail'][$j]['EventZIPCode'] =$arrResult[$key]->Events->Address->PostalCode;
                    $arrUpdatedResult['TrackDetail'][$j]['EventCountry'] =$arrResult[$key]->Events->Address->CountryCode;
                    $arrUpdatedResult['TrackDetail'][$j]['EventDesc'] = $arrResult[$key]->Events->EventDescription;

                    $j++;
                }
            }
        }
        return $arrUpdatedResult;
    }
}

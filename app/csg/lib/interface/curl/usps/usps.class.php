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
include_once(CSG_LIB_INTERFACE . 'curl/curl.class.php');

class clsUSPS extends clsCurlClient
{
    /**
     * Constructor of the class
     */
    public function __construct($wsdlPath = '', $className = '')
    {
        parent::__construct($wsdlPath);
    }

    /**
     * Function to get history of Tracking
     * @param string $strTrackingNumber
     * @return array $arrResult
     * @author Umesh W
     * @ Date:  17-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function getTrackingHistory($strTrackingNumber)
    {
        //$this->callService();//Call method from clsCurlClient.
        /*
          $strXMLContent ='<TrackFieldRequest USERID="'.CFG_USPS_USERID.'"><TrackID ID="'.$strTrackingNumber.'"></TrackID></TrackFieldRequest>';
          $strXMLContent ='API='.CFG_USPS_TRACKING_API.'&XML='.$strXMLContent;
          $arrResponse = $this->callService(CFG_USPS_TRACKING_URL, $strXMLContent);//Call method from clsCurlClient.
         */

        $strXMLContent = '<TrackFieldRequest USERID="' . CFG_USPS_USERID . '"><Revision>1</Revision><ClientIp>127.0.0.0</ClientIp><SourceId>1</SourceId><TrackID ID="' . $strTrackingNumber . '"></TrackID></TrackFieldRequest>';
        $strXMLContent = 'API=' . CFG_USPS_TRACKING_API . '&XML=' . $strXMLContent;
        // echo "===================== $strXMLContent ====================== \r\n";
        $arrResponse = $this->callService(CFG_USPS_TRACKING_URL, $strXMLContent); //Call method from clsCurlClient.
        // print_r($arrResponse); // exit;
        if (array_key_exists('Error', $arrResponse['TrackResponse']['TrackInfo'])) {
            $arrResponseToStopShipper = array("The tracking number may be incorrect or the status update is not yet available. Please verify your tracking number and try again later.", "A status update is not yet available on your package. It will be available when the shipper provides an update or the package is delivered to USPS. Check back soon. Sign up for Informed Delivery<SUP>&reg;</SUP> to receive notifications for packages addressed to you.");
            $strResponse = $arrResponse['TrackResponse']['TrackInfo']['Error']['Description'];
            if (in_array($strResponse, $arrResponseToStopShipper)) {
                $ret['stopCode'] = "Shipment not found or Tracking Number not valid";
                return $ret;
            } else {
                return new SoapFault($arrResponse['TrackResponse']['TrackInfo']['Error']['Number'], "Client", "getTrackingHistory", $arrResponse['TrackResponse']['TrackInfo']['Error']['Description']);
            }
        } elseif (array_key_exists('Error', $arrResponse)) {
            return new SoapFault($arrResponse['Error']['Number'], "Client", "getTrackingHistory", $arrResponse['Error']['Description']);
        } else {
            $arrResult = $this->processResponse($arrResponse);
            return array($arrResult);
        }
    }

    /**
     * Function to process the response
     * @param array $arrResponse
     * @return array $arrUpdatedResult
     * @author Umesh W
     * @ Date:  17-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function processResponse($arrResponse)
    {
        $arrUpdatedResult = array();
        if (is_array($arrResponse) && isset($arrResponse)) {
            $arrResult = $arrResponse['TrackResponse']['TrackInfo'];
            $arrEvents = array();
            $arrUpdatedResult['TrackSummary']['EventCity'] = $arrResult['TrackSummary']['EventCity'];
            $arrUpdatedResult['TrackSummary']['EventState'] = (is_array($arrResult['TrackSummary']['EventState'])) ? '' : $arrResult['TrackSummary']['EventState'];
            $arrUpdatedResult['TrackSummary']['EventZIPCode'] = (is_array($arrResult['TrackSummary']['EventZIPCode'])) ? '' : $arrResult['TrackSummary']['EventZIPCode'];
            $arrUpdatedResult['TrackSummary']['EventResidential'] = "";
            $arrUpdatedResult['TrackSummary']['EventCountry'] = (is_array($arrResult['TrackSummary']['EventCountry'])) ? "" : $arrResult['TrackSummary']['EventCountry'];
            $arrUpdatedResult['TrackSummary']['EventSummaryDesc'] = $arrResult['TrackSummary']['Event'];
            $arrUpdatedResult['TrackSummary']['ServiceType'] = $arrResult['ClassOfMailCode'];
            $arrUpdatedResult['TrackSummary']['CarrierCode'] = "USPS";

            $expectedDeliveryDate = (isset($arrResult['ExpectedDeliveryDate'])) ? date('Y-m-d', strtotime($arrResult['ExpectedDeliveryDate'])) : '0000-00-00';

            if ($expectedDeliveryDate == '0000-00-00') {
                $expectedDeliveryDate = (isset($arrResult['PredictedDeliveryDate'])) ? date('Y-m-d', strtotime($arrResult['PredictedDeliveryDate'])) : '';
            }

            $eventDateTime = date('Y-m-d', strtotime($arrResult['TrackSummary']['EventDate'])) . " " . date('H:i:s', strtotime($arrResult['TrackSummary']['EventTime']));
            $arrUpdatedResult['TrackSummary']['EstimatedDeliveryDate'] = $expectedDeliveryDate;
            $arrUpdatedResult['TrackSummary']['EventSummaryDateTime'] = $eventDateTime;
            $desc = explode(',', $arrUpdatedResult['TrackSummary']['EventSummaryDesc']);
            if ($arrResult['StatusCategory'] == 'Delivered' || stristr($arrResult['Status'], 'Delivered') !== false || $desc[0] == 'Delivered') {
                $arrUpdatedResult['TrackSummary']['EventDeliveryDesc'] = $desc[0];
                $arrUpdatedResult['TrackSummary']['ActualDeliveryDate'] = $eventDateTime;
            } else {
                $arrUpdatedResult['TrackSummary']['EventDeliveryDesc'] = '';
            }

            $arrEvents = array();
            if (!isset($arrResult['TrackDetail'][0])) {
                $arrEvents['TrackDetail'] = array($arrResult['TrackDetail']);
            } else {
                $arrEvents['TrackDetail'] = $arrResult['TrackDetail'];
            }
            foreach ($arrEvents['TrackDetail'] as $key => $val) {
                $arrUpdatedResult['TrackDetail'][$key]['EventDate'] = $arrEvents['TrackDetail'][$key]['EventDate'];
                $arrUpdatedResult['TrackDetail'][$key]['EventTime'] = (is_array($arrEvents['TrackDetail'][$key]['EventTime'])) ? "" : $arrEvents['TrackDetail'][$key]['EventTime'];
                $arrUpdatedResult['TrackDetail'][$key]['EventCity'] = (is_array($arrEvents['TrackDetail'][$key]['EventCity'])) ? "" : $arrEvents['TrackDetail'][$key]['EventCity'];
                $arrUpdatedResult['TrackDetail'][$key]['EventState'] = (is_array($arrEvents['TrackDetail'][$key]['EventState'])) ? "" : $arrEvents['TrackDetail'][$key]['EventState'];
                $arrUpdatedResult['TrackDetail'][$key]['EventZIPCode'] = (is_array($arrEvents['TrackDetail'][$key]['EventZIPCode'])) ? "" : $arrEvents['TrackDetail'][$key]['EventZIPCode'];
                $arrUpdatedResult['TrackDetail'][$key]['EventCountry'] = (is_array($arrEvents['TrackDetail'][$key]['EventCountry'])) ? "" : $arrEvents['TrackDetail'][$key]['EventCountry'];
                $arrUpdatedResult['TrackDetail'][$key]['EventDesc'] = $arrEvents['TrackDetail'][$key]['Event'];
                $arrUpdatedResult['TrackSummary']['EventDate'] = $arrEvents['TrackDetail'][$key]['EventDate'];
                $arrUpdatedResult['TrackSummary']['EventTime'] = (is_array($arrEvents['TrackDetail'][$key]['EventTime'])) ? "" : $arrEvents['TrackDetail'][$key]['EventTime'];
                $arrEvents[$val['Event']] = date('Y-m-d', strtotime($val['EventDate'])) . "T" . date('H:i:s', strtotime($arrUpdatedResult['TrackSummary']['EventTime']));
                $arrUpdatedResult['TrackSummary']['Events'] = $arrEvents;
            }
            /*
             * Creating Events array - harshada khire  26 Feb 2020
             */
            foreach ($arrUpdatedResult['TrackSummary']['Events'] as $key => $value) {
                $arrUpdatedResult['TrackSummary']['Events']['ACTUAL_PICKUP'] = '';
                $event = $key;
                $arrScanEvents = array('Accepted at USPS Origin Facility','Arrived at USPS Regional Origin Facility','Arrived at USPS Regional Facility','In Transit to Next Facility');
                if (in_array($event, $arrScanEvents)) {
                    $arrScanDate[] = date('Y-m-d', strtotime($value));
                }
            }
            if (!empty($arrScanDate)) {
                $arrUpdatedResult['TrackSummary']['Events']['ACTUAL_PICKUP'] = min($arrScanDate);
            }
        }

        return $arrUpdatedResult;
    }
}

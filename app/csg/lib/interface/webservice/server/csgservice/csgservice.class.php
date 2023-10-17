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
include_once(CSG_LIB_MODULES.'shipping/shipping.class.php');

class clsShippingGatewayController
{
    private $mObjShipping;

    public function __construct()
    {
        $this->mObjShipping	=	new clsShipping();
    }

    /**
     * Function to fetch the tracking history
     * @param array $arrParams
     * @return array $arrUpdatedResult
     * @author Umesh W
     * @ Date:  17-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function getTrackingHistory($arrParams)
    {
        $arrTracking =  $this->mObjShipping->getTrackingHistory($arrParams->strShippingCompanyCode, $arrParams->strTrackingNumber);
        if (is_soap_fault($arrTracking)) {
            return $arrTracking;
        } else {
            $res=new stdClass();
            $res->CurrentStatus=$arrTracking['data'][0]['TrackSummary']['EventSummaryDesc'];
            $res->ScheduledDeliveryDate=($arrTracking['data'][0]['TrackSummary']['EstimatedDeliveryDate']) ? $arrTracking['data'][0]['TrackSummary']['EstimatedDeliveryDate'] : "";
            $res->ScheduledDeliveryTime=($arrTracking['data'][0]['TrackSummary']['EstimatedDeliveryTime']) ? $arrTracking['data'][0]['TrackSummary']['EstimatedDeliveryTime'] : "";
            $res->ShippedOnDate=($arrTracking['data'][0]['TrackSummary']['EventDate']) ? $arrTracking['data'][0]['TrackSummary']['EventDate'] : "";
            $res->ShippedOnTime=$arrTracking['data'][0]['TrackSummary']['EventTime'];
            $res->ShippedToCity=$arrTracking['data'][0]['TrackSummary']['EventCity'];
            $res->ShippedToState=$arrTracking['data'][0]['TrackSummary']['EventState'];
            $res->ShippedToZIPCode=$arrTracking['data'][0]['TrackSummary']['EventZIPCode'];
            $res->ShippedToCountry=$arrTracking['data'][0]['TrackSummary']['EventCountry'];
            $res->ShippedToResidential=$arrTracking['data'][0]['TrackSummary']['EventResidential'];
            $res->ServiceType=($arrTracking['data'][0]['TrackSummary']['ServiceType']) ? $arrTracking['data'][0]['TrackSummary']['ServiceType'] : "";
            $res->CarrierCode=($arrTracking['data'][0]['TrackSummary']['CarrierCode']) ? $arrTracking['data'][0]['TrackSummary']['CarrierCode'] : "";
            $event=new stdClass();

            $arrCount = count($arrTracking['data'][0]['TrackDetail']);
            if ($arrCount > 0) {
                for ($i=0; $i<$arrCount; $i++) {
                    $event=new stdClass();
                    foreach ($arrTracking['data'][0]['TrackDetail'][$i] as $key=>$value) {
                        $value=htmlspecialchars($value);
                        $event->$key = $value;
                    }
                    $res->Event[]=$event;
                }
            }

            return $res;
        }
    }

    /**
     * Function to create XML from tracking array
     * @param array $arrTracking
     * @return string $responseXML
     * @author Umesh W
     * @ Date:  17-JUNE-2010
     * @ Reviewed by: TBD
     */

    private function createXML($arrTracking)
    {
        $responseXML ="";
        $responseXML ="<getTrackingHistoryResult><CurrentStatus>".$arrTracking['data'][0]['TrackSummary']['EventSummaryDesc']."</CurrentStatus>";
        $arrCount = count($arrTracking['data'][0]['TrackDetail']);
        $responseXML .="<Event>";
        if ($arrCount > 0) {
            for ($i=0; $i<$arrCount; $i++) {
                foreach ($arrTracking['data'][0]['TrackDetail'][$i] as $key=>$value) {
                    $value=htmlspecialchars($value);
                    $responseXML .= "<$key>$value</$key>";
                }
            }
        }
        $responseXML .= "</Event></getTrackingHistoryResult>";
        return $responseXML;
    }
}

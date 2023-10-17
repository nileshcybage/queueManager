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
include_once(CSG_LIB_INTERFACE.'curl/curl.class.php');
class clsUPS extends clsCurlClient
{
    /**
     * Constructor of the class
     */
    private $arrStatus;

    public function __construct($wsdlPath='', $className='')
    {
        // Second parameter true as we want to bypass the SSL Cerificate errors
        parent::__construct($wsdlPath, true);
        $this->arrStatus = array('I'=>'In Transit','D'=>'Delivered','X'=>'Exception','P'=>'Pickup','M'=>'Manifest Pickup');
    }
    public function getTrackingHistory($trackId)
    {
        //$this->callService();//Call method from clsCurlClient.
        //Testing
        //$trackId = (strtolower($trackId) == "test")?'EJ958088694US':$trackId;
        $strXMLContent ="<AccessRequest xml:lang='en-US'>
                            <AccessLicenseNumber>".CFG_UPS_UPS_ACCESSNUMBER."</AccessLicenseNumber>
                            <UserId>".CFG_UPS_USERNAME."</UserId>
                            <Password>".CFG_UPS_PASSWORD."</Password>
                        </AccessRequest>
                        <TrackRequest>
                            <Request>
                                <TransactionReference>
                                    <CustomerContext>
                                        <InternalKey>blah</InternalKey>
                                    </CustomerContext>
                                    <XpciVersion>1.0</XpciVersion>
                                </TransactionReference>
                                <RequestAction>Track</RequestAction>
                                <RequestOption>1</RequestOption>
                            </Request>
                            <IncludeFreight>01</IncludeFreight>
                            <TrackingNumber>
                                $trackId
                            </TrackingNumber>
                        </TrackRequest>";
        $response = $this->callService(CFG_UPS_TRACKING_URL, $strXMLContent);//Call method from clsCurlClient.

        if (array_key_exists('Error', $response['TrackResponse']['Response'])) {
            $eResArr = array();
            $eResArr = $response['TrackResponse']['Response']['Error'];
            $arrResponseToStopShipper = array("No information found", "No tracking information available", "Invalid data found or the combination of data elements is invalid", "Invalid tracking number");
            $strResponse = $eResArr['ErrorDescription'];
            if (in_array($strResponse, $arrResponseToStopShipper)) {
                $ret['stopCode'] = "Shipment not found or Tracking Number not valid";
                return $ret;
            } else {
                return new SoapFault($eResArr['ErrorCode'], "Client", "getTrackingHistory", $eResArr['ErrorDescription']);
            }
        } else {
            $res = $this->processResponse($response);

            return array($res);
        }
    }

    public function processResponse($response)
    {
        $newres = array();
        $arrActivityResult = array();
        $arrEvents = array();

        if (is_array($response) && isset($response)) {
            $res = $response['TrackResponse']['Shipment'];
            $arrShipTo = $res['ShipTo'];
            $arrActivities = $res['Package']['Activity'];
            $arrLastActivity = $res['Package']['Activity'][0];

            foreach ($arrActivities as $activity) {
                $eventDate = '';
                $eventTime = '';
                if (isset($activity['Date']) && $activity['Date']!='') {
                    $eventDate = date('F j, Y', strtotime($activity['Date']));
                }
                if (isset($activity['Time']) && $activity['Time'] !='') {
                    $eventTime = date('h:i a', strtotime($activity['Time']));
                }
                $newres['EventDate']    = $eventDate;
                $newres['EventTime']    = $eventTime;
                $newres['EventCity']    = $activity['ActivityLocation']['Address']['City'];
                $newres['EventState']   = $activity['ActivityLocation']['Address']['StateProvinceCode'];
                $newres['EventZIPCode'] = $activity['ActivityLocation']['Address']['PostalCode'];
                $newres['EventCountry'] = $activity['ActivityLocation']['Address']['CountryCode'];
                $newres['EventDesc']    = $activity['Status']['StatusType']['Description'];

                $arrActivityResult['TrackDetail'][] = $newres;
                $statusCodes[] = strtoupper($activity['Status']['StatusType']['Code']);
                /*
                 * Creating Events array - harshada khire  26 Feb 2020
                 */
                $arrEvents[$activity['Status']['StatusType']['Description']] = date('Y-m-d', strtotime($activity['Date']))."T".date('H:i:s', strtotime($activity['Time']));
                if ($arrEvents['Delivered'] === $arrEvents[$activity['Status']['StatusType']['Description']]) {
                    $arrActivityResult['TrackSummary']['ActualDeliveryDate'] = date('Y-m-d', strtotime($activity['Date']))." ".date('H:i:s', strtotime($activity['Time']));
                }
                $arrActivityResult['TrackSummary']['Events'] = $arrEvents;
            }
            // Recent status will be the Summary Status.
            $arrActivityResult['TrackSummary']['EventSummaryDesc'] = $this->arrStatus[$statusCodes[0]];
            $pickupDate = '';
            $pickupTime = '';
            $schDelDate = '';
            if (isset($res['PickupDate']) && $res['PickupDate'] != '') {
                $pickupDate = date('F j, Y', strtotime($res['PickupDate']));
                $actualPickup = date('Y-m-d', strtotime($res['PickupDate']));
            }
            if (isset($newres['EventTime']) && $newres['EventTime'] != '') {
                $pickupTime = date('h:i a', strtotime($newres['EventTime']));
            }
            if (isset($res['ScheduledDeliveryDate']) && $res['ScheduledDeliveryDate'] != '') {
                $schDelDate = date('F j, Y', strtotime($res['ScheduledDeliveryDate']));
            }
            if (isset($arrLastActivity['Date']) && !empty($arrLastActivity['Date'])) {
                $eventSummaryDateTime = date('Y-m-d', strtotime($arrLastActivity['Date']))." ".date('H:i:s', strtotime($arrLastActivity['Time']));
            }
            $arrActivityResult['TrackSummary']['Events']['ACTUAL_PICKUP']= $actualPickup;
            $arrActivityResult['TrackSummary']['EventCity']             = $arrShipTo['Address']['City'];
            $arrActivityResult['TrackSummary']['EventState']            = $arrShipTo['Address']['StateProvinceCode'];
            $arrActivityResult['TrackSummary']['EventZIPCode']          = $arrShipTo['Address']['PostalCode'];
            $arrActivityResult['TrackSummary']['EventCountry']          = $arrShipTo['Address']['CountryCode'];
            $arrActivityResult['TrackSummary']['EventResidential']      = '';
            $arrActivityResult['TrackSummary']['EstimatedDeliveryDate'] = $arrActivityResult['TrackSummary']['ActualDeliveryDate'];
            $arrActivityResult['TrackSummary']['EstimatedDeliveryTime'] = '';
            $arrActivityResult['TrackSummary']['EventDate']             = $pickupDate;
            $arrActivityResult['TrackSummary']['EventTime']             = $pickupTime;
            $arrActivityResult['TrackSummary']['CarrierCode']           = $res['Service']['Description'];
            $arrActivityResult['TrackSummary']['ServiceType']           = $res['Service']['Description'];
            //$estimatedSummaryTime =  $this->getConvertedTime($arrLastActivity['EventTime'], true);
            $arrActivityResult['TrackSummary']['EventSummaryDateTime']  = $eventSummaryDateTime;
        }
        return $arrActivityResult;
    }
}

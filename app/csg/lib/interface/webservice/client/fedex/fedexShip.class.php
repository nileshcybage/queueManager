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
include_once(CSG_LIB_INTERFACE . 'webservice/client/client.class.php');
include_once(CFG_LIB_MODULES.'credits/creditsmaster.class.php');



class clsFedexShip extends clsSoapClient
{
    /**
     * Constructor of the class
     */
    public function __construct($version = 'V4')
    {
        //To override the empty values to default 'V4'
        if(empty($version)){
            $version = 'V4';
        }
        if ($version == 'V4') {
            parent::__construct(CFG_FEDEX_WSDL);
        } elseif ($version == 'V26') {
            parent::__construct(CEG_FEDEX_WSDL_CREATE_SHIPMENT);
        } else {
            parent::__construct(CFG_FEDEX_WSDLV18);
        }
        $this->useVersion = $version;
    }

    public function getTrackingHistory($strTrackingNumber)
    {
        $fnToCall = 'getTrackingHistory' . $this->useVersion;
        return $this->$fnToCall($strTrackingNumber);
    }

    /**
     * Function to get history of Tracking
     * @param string $strTrackingNumber
     * @return array $arrResult
     * @author Sukhada Mahajan
     * @ Date:  16-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function getTrackingHistoryV4($strTrackingNumber)
    {

        /* ------------- Set the configuration values for FedEx service call -------------------------------------- */
        $arrRequest['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => CFG_FEDEX_KEY, 'Password' => CFG_FEDEX_PASSWORD));
        $arrRequest['ClientDetail'] = array('AccountNumber' => CFG_FEDEX_SHIPACCOUNT, 'MeterNumber' => CFG_FEDEX_METER);
        $arrRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request v4 using PHP ***');
        $arrRequest['Version'] = array('ServiceId' => 'trck', 'Major' => '4', 'Intermediate' => '0', 'Minor' => '0');
        $arrRequest['PackageIdentifier'] = array('Value' => $strTrackingNumber, 'Type' => CFG_FEDEX_TYPE);

        /* ------------ Catch the soap fault  ------------ */
        try {
            $arrResponse = $this->callService('track', $arrRequest); //Call method from clsSOAPClient.
            $arrOut = $this->xml2array($arrResponse);
            if ($arrResponse->HighestSeverity != 'FAILURE' && $arrResponse->HighestSeverity != 'ERROR') {
                $arrResult = $this->processResponse($arrResponse);
                return array($arrResult);
            } else {
                // echo 'else<br>';
                return new SoapFault($arrResponse->Notifications->Code, "Client", "getTrackingHistory", $arrResponse->Notifications->Message);
            }
        } catch (SoapFault $exception) {
            //  echo 'exceptiom';
            return new SoapFault($arrResponse->Notifications->Code, "Client", "getTrackingHistory", $arrResponse->Notifications->Message);
        }
    }

    public function getTrackingHistoryV18($strTrackingNumber)
    {
        /* ------------- Set the configuration values for FedEx service call -------------------------------------- */

        $arrRequest['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => CFG_FEDEX_KEY, 'Password' => CFG_FEDEX_PASSWORD), 'ParentCredential' => array('Key' => CFG_FEDEX_KEY, 'Password' => CFG_FEDEX_PASSWORD));
        $arrRequest['ClientDetail'] = array('AccountNumber' => CFG_FEDEX_SHIPACCOUNT, 'MeterNumber' => CFG_FEDEX_METER);
        $arrRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request v18 using PHP ***');
        $arrRequest['Version'] = array('ServiceId' => 'trck', 'Major' => '18', 'Intermediate' => '0', 'Minor' => '0');        
        // Setting up multiple tracking numbers https://usautoparts.atlassian.net/browse/LAIT-2516
        $arrRequest['SelectionDetails'] = [];
        $blnSingleTrackingString = false;
        if(is_array($strTrackingNumber) && count($strTrackingNumber) >  0){
            foreach($strTrackingNumber as $trackingNumber){
                $arrPackageIdentifier['PackageIdentifier'] = array('Value' => $trackingNumber, 'Type' => 'TRACKING_NUMBER_OR_DOORTAG');
                $arrRequest['SelectionDetails'][] = $arrPackageIdentifier;
            }
        }else{
            # Check if string
            $blnSingleTrackingString = true;
            $strTrackingNumber = (is_array($strTrackingNumber))?$strTrackingNumber[0]:$strTrackingNumber;            
            $arrPackageIdentifier['PackageIdentifier'] = array('Value' => $strTrackingNumber, 'Type' => 'TRACKING_NUMBER_OR_DOORTAG');
            $arrRequest['SelectionDetails'][] = $arrPackageIdentifier;
        }

        /* ------------ Catch the soap fault  ------------ */
        try {
            $arrResponse = $this->callService('track', $arrRequest); //Call method from clsSOAPClient.
            $arrOut = $this->xml2array($arrResponse);
            if ($arrResponse->HighestSeverity != 'FAILURE' && $arrResponse->HighestSeverity != 'ERROR') {
                if ($arrResponse->CompletedTrackDetails->TrackDetails->Notification->Severity === 'ERROR') {
                    $arrResponseToStopShipper = array("This tracking number cannot be found. Please check the number or contact the sender.", "Invalid tracking numbers. Please check the following numbers and resubmit.");
                    $strResponse = $arrResponse->CompletedTrackDetails->TrackDetails->Notification->Message;
                    if (in_array($strResponse, $arrResponseToStopShipper)) {
                        $ret['stopCode'] = "Shipment not found or Tracking Number not valid";
                        return $ret;
                    } else {
                        return new SoapFault($arrResponse->CompletedTrackDetails->TrackDetails->Notification->Severity, "Client", "getTrackingHistory", $arrResponse->CompletedTrackDetails->TrackDetails->Notification->Message);
                    }
                } else {
                    return $arrResult = $this->processResponseV18($arrResponse, $blnSingleTrackingString);
                    //return array($arrResult);
                }
            } else {
                return new SoapFault($arrResponse->Notifications->Code, "Client", "getTrackingHistory", $arrResponse->Notifications->Message);
            }
        } catch (SoapFault $exception) {
            //  echo 'exceptiom';
            return new SoapFault($arrResponse->Notifications->Code, "Client", "getTrackingHistory", $arrResponse->Notifications->Message);
        }
    }

    public function processResponseV18($arrResponse, $blnSingleTrackingString = false)
    {
        $arrResult = $arrResponse->CompletedTrackDetails;

        /* ------ The FedEx response varies for some tracking numbers. So taking the response in array everytime -------- */
        if (!is_array($arrResult)) {
            $arrResult = array($arrResult);
        }

        $j = 0;
        if (is_array($arrResult) && isset($arrResult)) {
            $finalResult = array();
            foreach ($arrResult as $key => $val) {
                $arrDestinationAddress = $arrResult[$key]->TrackDetails->DestinationAddress;                
                $trackingDetails = [];
                if ($arrDestinationAddress->City != "" || $arrDestinationAddress->StateOrProvinceCode != "" || $arrDestinationAddress->CountryCode != "" || $arrDestinationAddress->Residential != "") {
                    //LASTEST STSTUS WILL COME LAST

                    $trackingDetails['EventCity'] = $arrResult[$key]->TrackDetails->StatusDetail->Location->City;
                    $trackingDetails['EventState'] = $arrResult[$key]->TrackDetails->StatusDetail->Location->StateOrProvinceCode;
                    $trackingDetails['EventZIPCode'] = '';
                    $trackingDetails['EventCountry'] = $arrResult[$key]->TrackDetails->StatusDetail->Location->CountryCode;
                    $trackingDetails['EventResidential'] = $arrResult[$key]->TrackDetails->StatusDetail->Location->Residential;
                    $trackingDetails['EventSummaryDesc'] = $arrResult[$key]->TrackDetails->StatusDetail->Description;
                    $trackingDetails['EventSummaryDateTime'] = $arrResult[$key]->TrackDetails->StatusDetail->CreationTime;

                    $trackingDetails['CarrierCode'] = $arrResult[$key]->TrackDetails->CarrierCode;
                    $trackingDetails['ServiceType'] = $arrResult[$key]->TrackDetails->Service->Type;

                    # Initialize array MGR-10466
                    $shipmentTracking = array();

                    foreach ($arrResult[$key]->TrackDetails->DatesOrTimes as $arrEventObject) {
                        $shipmentTracking[$arrEventObject->Type] = $arrEventObject->DateOrTimestamp;
                    }

                    $arrDateTime = array();
                    if (isset($shipmentTracking['ACTUAL_DELIVERY'])) {
                        $arrDateTime = explode("T", $shipmentTracking['ACTUAL_DELIVERY']);
                        $trackingDetails['ActualDeliveryDate'] = $shipmentTracking['ACTUAL_DELIVERY'];
                    } elseif (isset($shipmentTracking['ESTIMATED_DELIVERY']) > 0) {
                        $arrDateTime = explode("T", $shipmentTracking['ESTIMATED_DELIVERY']);
                    }
                    if (!empty($arrDateTime)) {
                        $EstimatedDeliverydatetime = date_create($arrDateTime[0] . '' . $arrDateTime[1]);
                        $trackingDetails['EstimatedDeliveryDate'] = date_format($EstimatedDeliverydatetime, 'F j, Y');
                        $trackingDetails['EstimatedDeliveryTime'] = date_format($EstimatedDeliverydatetime, 'h:i a');
                    }
                    $trackingDetails['Events'] = $shipmentTracking;

                    $j++;
                    $finalResult[$arrResult[$key]->TrackDetails->TrackingNumber]=$trackingDetails;
                }
            }
        }
        # Handling for Single tracking which is string only. It will mostly come from CSG Service
        if($blnSingleTrackingString == true){
            $singleTrackingDetails[]['TrackSummary'] = array_values($finalResult)[0];
            return $singleTrackingDetails;
        }
        return $finalResult;
    }

    public function convertUTCtoEST($utcDateTime)
    {
        $date = new DateTime($utcDateTime, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/New_York'));
        return $date->format('Y-m-d H:i:s');
    }

    public function xml2array($xmlObject, $out = array())
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xml2array($node) : $node;
        }

        return $out;
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

        /* ------ The FedEx response varies for some tracking numbers. So taking the response in array everytime -------- */
        if (!is_array($arrResult)) {
            $arrResult = array($arrResult);
        }

        $j = 0;
        if (is_array($arrResult) && isset($arrResult)) {
            foreach ($arrResult as $key => $val) {
                $arrDestinationAddress = $arrResult[$key]->DestinationAddress;

                if ($arrDestinationAddress->City != "" || $arrDestinationAddress->StateOrProvinceCode != "" || $arrDestinationAddress->CountryCode != "" || $arrDestinationAddress->Residential != "") {
                    //LASTEST STSTUS WILL COME LAST

                    $arrUpdatedResult['TrackSummary']['EventCity'] = $arrDestinationAddress->City;
                    $arrUpdatedResult['TrackSummary']['EventState'] = $arrDestinationAddress->StateOrProvinceCode;
                    $arrUpdatedResult['TrackSummary']['EventZIPCode'] = $arrDestinationAddress->PostalCode;
                    $arrUpdatedResult['TrackSummary']['EventCountry'] = $arrDestinationAddress->CountryCode;
                    $arrUpdatedResult['TrackSummary']['EventResidential'] = $arrDestinationAddress->Residential;
                    $arrUpdatedResult['TrackSummary']['EventSummaryDesc'] = $arrResult[$key]->StatusDescription;
                    $arrEstimatedDeliveryTimestamp = explode("T", $arrResult[$key]->EstimatedDeliveryTimestamp);
                    if (count($arrEstimatedDeliveryTimestamp) > 0) {
                        $EstimatedDeliverydatetime = date_create($arrEstimatedDeliveryTimestamp[0] . '' . $arrEstimatedDeliveryTimestamp[1]);
                        $arrUpdatedResult['TrackSummary']['EstimatedDeliveryDate'] = date_format($EstimatedDeliverydatetime, 'F j, Y');
                        $arrUpdatedResult['TrackSummary']['EstimatedDeliveryTime'] = date_format($EstimatedDeliverydatetime, 'h:i a');
                    }
                    $arrUpdatedResult['TrackSummary']['CarrierCode'] = $arrResult[$key]->CarrierCode;
                    $arrUpdatedResult['TrackSummary']['ServiceType'] = $arrResult[$key]->ServiceType;
                    $arrDateTime = explode("T", $arrResult[$key]->ShipTimestamp);

                    if (count($arrDateTime) > 0) {
                        $datetime = date_create($arrDateTime[0] . '' . $arrDateTime[1]);
                        $arrUpdatedResult['TrackDetail'][$j]['EventDate'] = date_format($datetime, 'F j, Y');
                        $arrUpdatedResult['TrackDetail'][$j]['EventTime'] = date_format($datetime, 'h:i a');
                        $arrUpdatedResult['TrackSummary']['EventDate'] = date_format($datetime, 'F j, Y');
                        $arrUpdatedResult['TrackSummary']['EventTime'] = date_format($datetime, 'h:i a');
                    }

                    $arrUpdatedResult['TrackDetail'][$j]['EventCity'] = $arrResult[$key]->Events->Address->City;
                    $arrUpdatedResult['TrackDetail'][$j]['EventState'] = $arrResult[$key]->Events->Address->StateOrProvinceCode;
                    $arrUpdatedResult['TrackDetail'][$j]['EventZIPCode'] = $arrResult[$key]->Events->Address->PostalCode;
                    $arrUpdatedResult['TrackDetail'][$j]['EventCountry'] = $arrResult[$key]->Events->Address->CountryCode;
                    $arrUpdatedResult['TrackDetail'][$j]['EventDesc'] = $arrResult[$key]->Events->EventDescription;

                    $j++;
                }
            }
        }
        return $arrUpdatedResult;
    }
    //Manager to integrate with FedEx Return Shipping Label Nikhil Nagnurwar 8th Dec 20202
    public function CreateShipment($arrOrderInfo, $arrWareHouseDetails, $arrCreditProducts, $arrReturnShippingDecisionDetails, $apiRequiredData)
    {
        if ($arrReturnShippingDecisionDetails['total_weight'] == 0) {
            $arrReturnShippingDecisionDetails['total_weight'] = 5;
        }

        $arrRequest['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => CFG_FEDEX_SHIPPING_KEY, 'Password' => CFG_FEDEX_SHIPPING_PASSWORD),
            'ParentCredential' => array('Key' => CFG_FEDEX_SHIPPING_KEY, 'Password' => CFG_FEDEX_SHIPPING_PASSWORD));

        $arrRequest['ClientDetail'] = array('AccountNumber' => CFG_FEDEX_SHIPPING_SHIPACCOUNT, 'MeterNumber' => CFG_FEDEX_SHIPPING_METER);

        $arrRequest['Version'] = array('ServiceId' => 'ship', 'Major' => '26', 'Intermediate' => '0', 'Minor' => '0');

        $arrRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Domestic Shipping Request - Master using PHP ***');


        $timestamp = new DateTime('+'. TIME_STAMP_DAYS .'days');
        $date = date("Y-m-d");// current date

        $expirationDate = $apiRequiredData['expirationTimeStamp'];
        $arrRequest['RequestedShipment'] = array('ShipTimestamp' => $timestamp->format(DateTime::ATOM), 'DropoffType' => 'REGULAR_PICKUP',
                                          'ServiceType' => 'FEDEX_GROUND', 'PackagingType' => 'YOUR_PACKAGING',
                                          'Shipper' => array('Contact' => array('PersonName' => $arrOrderInfo['customers_first_name']." ".$arrOrderInfo['customers_last_name'],
                                          'CompanyName' => $arrOrderInfo['customers_first_name']." ".$arrOrderInfo['customers_last_name'],
                                          'PhoneNumber' => $arrOrderInfo['customers_telephone']), 'Address' => array('StreetLines' => $arrOrderInfo['customers_street_address'],
                                          'City' => $arrOrderInfo['customers_city'],
                                          'StateOrProvinceCode' => $arrOrderInfo['customers_state'], 'PostalCode' => $arrOrderInfo['customers_postcode'],
                                          'CountryCode' => $arrOrderInfo['customers_country'])),
                                          'Recipient' => array('Contact' => array('PersonName' => $arrWareHouseDetails[0]['vendor_name'],
                                          'CompanyName' => $arrWareHouseDetails[0]['vendor_name'], 'PhoneNumber' => $arrOrderInfo['delivery_telephone']),
                                          'Address' => array('StreetLines' => $arrWareHouseDetails[0]['address'], 'City' => $arrWareHouseDetails[0]['city'],
                                          'StateOrProvinceCode' => $arrWareHouseDetails[0]['state'], 'PostalCode' => $arrWareHouseDetails[0]['zip'], 'CountryCode' => 'US',
                                              'Residential'=> false
                                          )),


                                         'ShippingChargesPayment' => array('PaymentType' => 'SENDER', 'Payor' => array('ResponsibleParty' => array('AccountNumber' => CFG_FEDEX_SHIPPING_SHIPACCOUNT,
                                             'Address' => array('CountryCode' => 'US')))),

                                         'SpecialServicesRequested' => array('SpecialServiceTypes' => 'RETURN_SHIPMENT','ReturnShipmentDetail' => array('ReturnType' => 'PRINT_RETURN_LABEL'),
                                                                               'PendingShipmentDetail'=>array('ExpirationDate' => $expirationDate,'Type' => 'EMAIL')),

                                          'LabelSpecification' => array('LabelFormatType' => 'COMMON2D', 'ImageType' => 'PDF', 'LabelStockType' => 'PAPER_4X9'),
                                                                      'PackageCount' => 1,'RateRequestTypes' => 'LIST');
        if (($apiRequiredData['length'] != 0) && ($apiRequiredData['width'] != 0) && ($apiRequiredData['height'] != 0)) {
            $arrRequest['RequestedShipment']['RequestedPackageLineItems'] = array('SequenceNumber' => '1', 'Weight' => array('Units' => 'LB', 'Value' => $arrReturnShippingDecisionDetails['total_weight']),
                        'Dimensions' => array('Length' => $apiRequiredData['length'],'Width' => $apiRequiredData['width'] ,'Height' => $apiRequiredData['height'],'Units' => 'IN'));
        } else {
            $arrRequest['RequestedShipment']['RequestedPackageLineItems'] = array('SequenceNumber' => '1', 'Weight' => array('Units' => 'LB', 'Value' => $arrReturnShippingDecisionDetails['total_weight'])
        );
        }
        $arrRequest['RequestedShipment']['RequestedPackageLineItems']['CustomerReferences'] = array('0' => array('CustomerReferenceType' => 'RMA_ASSOCIATION','Value' => $apiRequiredData['rma_number']),
                         '1' => array('CustomerReferenceType' => 'P_O_NUMBER', 'Value' => $apiRequiredData['vendedId']),
                            '2' =>array('CustomerReferenceType' => 'CUSTOMER_REFERENCE', 'Value' => $apiRequiredData['rma_number']));
                            $saveCsgDetails['request_payload'] = json_encode($arrRequest);
                            $saveCsgDetails['order_id'] =  $arrOrderInfo['order_id'];
                            $saveCsgDetails['credit_id'] = $arrCreditProducts[0]['credit_id'];
                            $saveCsgDetails['request_endpoint'] = CFG_FEDEX_API_URL;
                            $saveCsgDetails['request_method'] = "processShipment";
                            $saveCsgDetails['date_created'] = date('Y-m-d H:i:s');
                            $objCreditsMaster = new clsCreditsMaster();
                            $objCreditsMaster->mObjCredits->saveCSGDeatils($saveCsgDetails);
                            $start = microtime();
                          try {
        //changes related to MECM-78 Handling Service unavailable scenario while generating FedEx Shipping label starts here
                            $response = $this->callService('processShipment', $arrRequest);;
                          } 
                          catch (SoapFault $e) { 
                              $response = array();
                              $response['actual_error_message'] = $e->getMessage();
                          }
                          catch (Exception $e ) { 
                              $response = array();
                              $response['actual_error_message'] = $e->getMessage();
                          }
                            $arrResponse = array();
                            if(is_array($response)){
                                $arrResponse = $response;
                            }
                            else{
                                $arrResponse = json_decode(json_encode($response), true);
                            }
                            $strExecutionTime = $this->getFedexResponseTime($start);
                            $updateCsgDetails['response_execution'] = $strExecutionTime;
                            $updateCsgDetails['response_payload'] = json_encode($arrResponse);
                            $updateCsgDetails['tracking_number'] = (isset($arrResponse['CompletedShipmentDetail']['MasterTrackingId']['TrackingNumber'])) ? $arrResponse['CompletedShipmentDetail']['MasterTrackingId']['TrackingNumber'] : 0 ;
                            $where['credit_id'] = $arrCreditProducts[0]['credit_id'];
                            $where['date_created'] = $saveCsgDetails['date_created'];
                            $objCreditsMaster->mObjCredits->updateCSGDeatils($updateCsgDetails,$where);
        //ends here
        return $arrResponse;
    }
    public function xmlToArray($xml)
    {
        include_once('Unserializer.php');
        $objUnserializer = new XML_Unserializer();
        // Serialize the data structure
        $xml = trim($xml);
        $status = $objUnserializer->unserialize($xml);

        // Check whether serialization worked

        if ($status instanceof PEAR_Error) {
            die($status->getMessage());
        }

        $arrData = $objUnserializer->getUnserializedData();

        return $arrData;
    }
        /**
     * Function to getFedexAPIResponseTime
     * @param start_time
     * @return float 
     * @author Nikhil nagnurwar
     * @created  30-Nov-2021
     */
    function getFedexResponseTime($strStartTime)
   {
    $strEndTime = microtime();
    $arrStartTime = explode(' ', $strStartTime);
    $arrEndTime = explode(' ', $strEndTime);

    return number_format(($arrEndTime[1] + $arrEndTime[0] - ($arrStartTime[1] + $arrStartTime[0])), 3);
   }
}

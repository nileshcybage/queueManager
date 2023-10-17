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
class clsShipping
{
    private $mObjShippingInterface;

    /**
     * Function to process the response
     * @param string $strCompanyCode
     * @param string $strTrackingNumber
     * @return array $arrHistory
     * @author Sukhada Mahajan
     * @ Date:  18-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function getTrackingHistory($strCompanyCode, $strTrackingNumber)
    {
        if (trim($strCompanyCode) == '') {
            return new SoapFault("1003", "Server", "getTrackingHistory", "Shipping Company Code is missing");
        }

        if (trim($strTrackingNumber) == '') {
            return new SoapFault("1001", "Server", "getTrackingHistory", "Tracking Number is missing");
        }
        // Comment as production credentials are not available for UPS
        switch (strtoupper($strCompanyCode)) {
            case 'FEDEX':
                include_once(CSG_LIB_INTERFACE.'webservice/client/fedex/fedex.class.php');
                $this->mObjShippingInterface = new 	clsFedex();
                break;
            case 'UPS':
                include_once(CSG_LIB_INTERFACE.'curl/ups/ups.class.php');
                $this->mObjShippingInterface = new 	clsUPS();
                break;
            case 'USPS':
                include_once(CSG_LIB_INTERFACE.'curl/usps/usps.class.php');
                $this->mObjShippingInterface = new 	clsUSPS();
                break;
            default:
                return new SoapFault("1002", "Server", "getTrackingHistory", "Incorrect Shipping Company Code");
        }

        //Call get Tracking History method of the API.
        $arrHistory = $this->mObjShippingInterface->getTrackingHistory($strTrackingNumber);
        if (is_soap_fault($arrHistory)) {
            return $arrHistory;
        } else {
            return array("result"=>1, "data" => $arrHistory);
        }
    }
}

<?php
/**
 * Class : Wrapper for clsShipping
 */

/**
 * @author Rajendra Pawar
 * @created   08-Jan-2015 11:30 AM IST
 * @modified
 * @version 1.0
 * @reviewed by:
 * @package
 */
class clsShipmentWrapper
{
    private $mObjShippingInterface;

    /**
     * Function to process the response
     * @param string $strCompanyCode
     * @param array|string $strTrackingNumber
     * @return array $arrHistory
     * @author Sukhada Mahajan
     * @ Date:  18-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function getTrackingHistory($strCompanyCode, $strTrackingNumber, $fedExVersion = '')
    {
        if (trim($strCompanyCode) == '') {
            return new SoapFault("1003", "Server", "getTrackingHistory", "Shipping Company Code is missing");
        }
        //https://usautoparts.atlassian.net/browse/LAIT-2516
        if (trim($strTrackingNumber) == '') {
            if(!is_array($strTrackingNumber) && count($strTrackingNumber) < 0){
                return new SoapFault("1001", "Server", "getTrackingHistory", "Tracking Number is missing");
            }
        }
        // Comment as production credentials are not available for UPS
        switch (strtoupper($strCompanyCode)) {
            case 'FEDEX':
                include_once(CSG_LIB_INTERFACE.'webservice/client/fedex/fedexShip.class.php');
                $this->mObjShippingInterface = new clsFedexShip($fedExVersion);
                break;
            case 'UPS':
                include_once(CSG_LIB_INTERFACE.'curl/ups/ups.class.php');
                $this->mObjShippingInterface = new 	clsUPS();
                break;
            case 'USPS':
                include_once(CSG_LIB_INTERFACE.'curl/usps/usps.class.php');
                $this->mObjShippingInterface = new 	clsUSPS();
                break;
                        case 'GLS':
                                include_once(CSG_LIB_INTERFACE.'curl/gls/gls.class.php');
                $this->mObjShippingInterface = new clsGLS();
                break;
                        case 'LSO':
                                include_once(CSG_LIB_INTERFACE.'curl/lso/lso.class.php');
                $this->mObjShippingInterface = new clsLSO();
                break;

            default:
                return new SoapFault("1002", "Server", "getTrackingHistory", "Incorrect Shipping Company Code");
        }

        //Call get Tracking History method of the API.
        $arrHistory = $this->mObjShippingInterface->getTrackingHistory($strTrackingNumber);
        if (is_soap_fault($arrHistory) || $arrHistory === false) {
            return $arrHistory;
        } else {
            return array("result"=>1, "data" => $arrHistory);
        }
    }
    public function generateShippingLabel($fedExVersion, $arrOrderInfo, $arrWareHouseDetails, $arrCreditProducts, $arrReturnShippingDecisionDetails, $apiRequiredData)
    {
        include_once(CSG_LIB_INTERFACE . 'webservice/client/fedex/fedexShip.class.php');
        $this->mObjShippingInterface = new clsFedexShip($fedExVersion);
        $arrResponse = $this->mObjShippingInterface->CreateShipment($arrOrderInfo, $arrWareHouseDetails, $arrCreditProducts, $arrReturnShippingDecisionDetails, $apiRequiredData);
        return $arrResponse;
    }
}

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
class clsSoapClient
{
    private static $mObjClient;
    /**
     * Constructor of the class
     */
    public function __construct($wsdlPath)
    {
        if (empty(self::$mObjClient)) {
            self::$mObjClient = new SoapClient($wsdlPath, array('trace' => 1));
        }
        //$this->mObjClient = new SoapClient($wsdlPath, array('trace' => 1));
    }

    /**
     * Function to call the webservice
     * @param string $strMethod
     * @param array $arrParams
     * @return array $arrUpdatedResult
     * @author Sukhada Mahajan
     * @ Date:  16-JUNE-2010
     * @ Reviewed by: TBD
     */
    public function callService($strMethod, $arrParams)
    {
        //Call service
        $response = self::$mObjClient->$strMethod($arrParams);
        return $response;
    }
}

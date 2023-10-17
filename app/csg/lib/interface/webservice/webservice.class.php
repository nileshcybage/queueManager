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

class clsWebservice extends SoapServer
{
    /**
     * Constructor of the class
     */
    public function __construct($wsdlPath, $className)
    {
        parent::__construct($wsdlPath);

        $this->setClass($className);
    }
}

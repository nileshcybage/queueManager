<?php
/**
 * This file is the entry point for CPG Service
 * @author
 * @created
 * @reviewed by
 * @version 1.0
 * @package
 */

/**
 * Avoid direct access to service file
 */
include_once('../common.php');

include_once(CSG_LIB_INTERFACE.'webservice/webservice.class.php');
include_once(CSG_LIB_INTERFACE.'webservice/server/csgservice/csgservice.class.php');

ini_set("soap.wsdl_cache_enabled", "0");
$objSoapServer = new clsWebservice(CSG_WSDL.'csg.wsdl', 'clsShippingGatewayController');
$objSoapServer->handle();

<?php
/**
 * Global Configuration file
 *
 * This file will check the current environment and include the appropriate
 * config file accordingly.
 * @author Sukhada Mahajan
 * @created 16-JUNE-2010
 * @changed
 * @version 1.0
 * @package
 */

define('CSG_ROOT', str_replace('/config', '/', dirname(__FILE__))."/");
define('CSG_WSDL', CSG_ROOT.'www/wsdl/'); //absolute path of the lib/interface folder.
define('CSG_LIB_INTERFACE', CSG_ROOT.'lib/interface/');
define('CSG_LIB_MODULES', CSG_ROOT.'lib/modules/');

// Fedex constants
define('CFG_FEDEX_WSDL', CSG_WSDL.'TrackService_v4.wsdl');
define('CFG_FEDEX_KEY', 'qtjfRrzwKWTRdkol');
define('CFG_FEDEX_PASSWORD', '0OsK7Xc09iPxGCOQSNaDc0zxH');
define('CFG_FEDEX_SHIPACCOUNT', '248170999');
define('CFG_FEDEX_METER', '100578012');
define('CFG_FEDEX_BILLACCOUNT', 'XXX');
define('CFG_FEDEX_DUTYACCOUNT', 'XXX');
define('CFG_FEDEX_TYPE', 'TRACKING_NUMBER_OR_DOORTAG');

define('CFG_FEDEX_WSDLV18', CSG_WSDL.'TrackService_v18.wsdl');

// USPS constants
define('CFG_USPS_USERID', '252USAUT0902');
define('CFG_USPS_TRACKING_API', 'TrackV2');
//define('CFG_USPS_TRACKING_URL','http://production.shippingapis.com/ShippingAPI.dll'); //Production Path
define('CFG_USPS_TRACKING_URL', 'https://secure.shippingapis.com/ShippingAPI.dll'); //Production Path

define('CFG_UPS_UPS_ACCESSNUMBER', 'ACA1A7F3080AAA08');
define('CFG_UPS_USERNAME', 'usapupsaccount');
define('CFG_UPS_PASSWORD', 'USAutoParts1');
define('CFG_UPS_TRACKING_URL', 'https://onlinetools.ups.com/ups.app/xml/Track');

//GLS Constants...
define('CFG_GLS_USERNAME', 'USAutoPartsWS');
define('CFG_GLS_PASSWORD', 'kxPka72URu');
define('CFG_GLS_ACCOUNT_NUMBER', '52918');
define('CFG_GLS_TRACKING_URL', 'https://api.gso.com/Rest/v1/TrackShipment');
define('CFG_GLS_TOKEN_URL', 'https://api.gso.com/Rest/v1/token');

//Fedex Shipping label related constants
define('CFG_FEDEX_SHIPPING_KEY', 'A94gctqCbEYZnxuT');
define('CFG_FEDEX_SHIPPING_PASSWORD', 'ACxKWhLtyvg7D5YzFN7iFcz3V');
define('CFG_FEDEX_SHIPPING_SHIPACCOUNT', '443546308');
define('CFG_FEDEX_SHIPPING_METER', '252724391');
define('CEG_FEDEX_WSDL_CREATE_SHIPMENT', CSG_WSDL.'ShipService_v26.wsdl');
define('TIME_STAMP_DAYS', '7');
define('EXPIRATION_DAYS', '45');

//LSO Constants...
define('CFG_LSO_USERNAME', 'carparts');
define('CFG_LSO_PASSWORD', 'GatingEstimate4Payroll$Rundown');
define('CFG_LSO_ACCOUNT_NUMBER', '202676');
define('CFG_LSO_TRACKING_URL', 'https://services.lso.com/partnershippingservices/v1_5/TrackingService.asmx?WSDL');

<?php
/**
 * Global Common file
 *
 * This file will strictly contain code that needs to be executed when any manager script starts
 * like DB connection, log object, global object etc.
 * @author Sukhada Mahajan
 * @modified by
 * @created  16-Jun-10
 * @changed
 * @version 1.0
 * @package
 */
ini_set('display_errors', 0);
//error_reporting (E_ALL);
// Assign the start time of the script

include_once(dirname(__FILE__).'/config.inc.php');

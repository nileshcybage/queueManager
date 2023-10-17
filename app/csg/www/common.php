<?php
/**
 * Global Common file
 *
 * This file will strictly contain code that needs to be executed when any manager script starts
 * like DB connection, log object, global object etc.
 * @author Sukhada Mahajan
 * @modified by
 * @created 15-Jun-2010 16:10 PM PST
 * @changed
 * @version 1.0
 * @package
 */

// include_once(rtrim(dirname(__FILE__), 'www/').'/common.php');
include_once(str_replace("www", '', dirname(__FILE__)).'/common.php');

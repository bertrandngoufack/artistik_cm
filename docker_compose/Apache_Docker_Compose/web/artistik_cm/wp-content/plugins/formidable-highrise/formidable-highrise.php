<?php
/*
Plugin Name: Formidable Highrise
Description: Send Posted results to Highrise CRM
Plugin URI: http://formidablepro.com/
Author URI: http://strategy11.com
Author: Strategy11
Version: 1.06
*/

include_once(dirname(__FILE__) .'/helpers/FrmHrsAppHelper.php');
$obj = new FrmHrsAppHelper();

//Controllers
require_once(dirname(__FILE__) .'/controllers/FrmHrsAppController.php');
require_once(dirname(__FILE__) .'/controllers/FrmHrsSettingsController.php');

$obj = new FrmHrsAppController();
$obj = new FrmHrsSettingsController();
unset($obj);

/***** SETUP SETTINGS OBJECT *****/
require_once(dirname(__FILE__) .'/models/FrmHrsSettings.php');

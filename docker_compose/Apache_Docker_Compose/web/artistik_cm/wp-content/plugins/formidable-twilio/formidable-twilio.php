<?php
/*
Plugin Name: Formidable Twilio
Description: Accept SMS votes for a poll or send texts from forms
Version: 1.09
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
Text Domain: frmtwlo
*/


require_once dirname( __FILE__ ) . '/models/FrmTwloSettings.php';
require_once dirname( __FILE__ ) . '/controllers/FrmTwloAppController.php';
require_once dirname( __FILE__ ) . '/controllers/FrmTwloSettingsController.php';

$obj = new FrmTwloAppController();
$obj = new FrmTwloSettingsController();

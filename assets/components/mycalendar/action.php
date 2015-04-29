<?php
if (empty($_REQUEST['action'])) {
	die('Access denied');
}
else {
	$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
}

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/index.php';

$modx->getService('error','error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

$response = array('success' => false, 'message' =>'');
if (!empty($_REQUEST['instance'])) {
	$instance = filter_input(INPUT_POST, 'instance', FILTER_SANITIZE_SPECIAL_CHARS);
} else {
	$response['message'] = 'No instance is specified!';
	exit($response);
}
/** @var myCalendar $myCalendar */
$myCalendar = $modx->getService('mycalendar','myCalendar',MODX_CORE_PATH.'components/mycalendar/model/mycalendar/',$_SESSION['mycalendar'][$instance]['scriptProperties']);

if (!($myCalendar instanceof myCalendar)) {
	die($modx->toJSON(array('success' => false, 'message' =>'Error of class init!')));
}

switch ($action) {
	case 'getEvents':
		$response = $myCalendar->getEvents($_REQUEST);
		break;
	case 'openDlg':
		$response = $myCalendar->openDialog($_REQUEST);
		break;
	case 'saveEvent':
		$response = $myCalendar->saveEvent($_REQUEST);
		break;
	case 'removeEvent':
		$response = $myCalendar->removeEvent(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT));
		break;
	default:
		$response = array('success' => false, 'message' => 'Неопределенное действие!');
}

if (is_array($response)) {
	$response = $modx->toJSON($response);
}

@session_write_close();
exit($response);
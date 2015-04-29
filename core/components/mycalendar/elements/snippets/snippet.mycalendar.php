<?php
/** @var array $scriptProperties */
$scriptProperties['showWeekNumber'] = !empty($scriptProperties['showWeekNumber']) ? 'true' : 'false';
$scriptProperties['showWeekends'] = !empty($scriptProperties['showWeekends']) ? 'true' : 'false';
$scriptProperties['allDaySlot'] = !empty($scriptProperties['allDaySlot']) ? 'true' : 'false';
if ($scriptProperties['height'] == 'auto') $scriptProperties['height'] = "'auto'";
$scriptProperties['readOnly'] = !empty($scriptProperties['readOnly']) ? true : false;
$scriptProperties['fixedWeekCount'] = !empty($scriptProperties['fixedWeekCount']) ? 'true' : 'false';

if (empty($minTime)) $scriptProperties['minTime'] = '00:00';
if (empty($maxTime)) $scriptProperties['maxTime'] = '24:00';
if (empty($defaultColor)) $scriptProperties['defaultColor'] ='#0070c0';
if (empty($axisFormat)) $scriptProperties['axisFormat'] ='H:mm';
if (empty($defaultDuration)) $scriptProperties['defaultDuration'] = '00:30';
if (empty($tpl)) {$scriptProperties['tpl'] = 'tpl.myCalendar';}
if (empty($ctx)) {$ctx = $modx->context->get('key');}
if (empty($instance)) $instance = $scriptProperties['instance'] = 'mycalendar';

if (!$scriptProperties['allowGuestEdit'] && !$modx->user->isAuthenticated($ctx))
	$scriptProperties['readOnly'] = true;

$_SESSION['mycalendar'][$instance]['scriptProperties'] = $scriptProperties;

/** @var myCalendar $myCalendar */
$myCalendar = $modx->getService('mycalendar','myCalendar',MODX_CORE_PATH.'components/mycalendar/model/mycalendar/',$scriptProperties);
$myCalendar->initialize($modx->context->get('key'),$scriptProperties);
$output = "<div id=\"{$instance}\" class=\"mycalendar\"></div>";

return $output;
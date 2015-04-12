<?php
$scriptProperties['showWeekNumber'] = !empty($scriptProperties['showWeekNumber']) ? 'true' : 'false';
$scriptProperties['showWeekends'] = !empty($scriptProperties['showWeekends']) ? 'true' : 'false';
$scriptProperties['allDaySlot'] = !empty($scriptProperties['allDaySlot']) ? 'true' : 'false';
if ($scriptProperties['height'] == 'auto') $scriptProperties['height'] = "'auto'";
$scriptProperties['readOnly'] = !empty($scriptProperties['readOnly']) ? true : false;
$scriptProperties['fixedWeekCount'] = !empty($scriptProperties['fixedWeekCount']) ? 'true' : 'false';
if (empty($scriptProperties['minTime'])) {
	$scriptProperties['minTime'] = "'00:00'";
} else {
	$scriptProperties['minTime'] = $modx->quote($scriptProperties['minTime']);
}
if (empty($scriptProperties['maxTime'])) {
	$scriptProperties['maxTime'] = "'24:00'";
} else {
	$scriptProperties['maxTime'] = $modx->quote($scriptProperties['maxTime']);
}

$scriptProperties['axisFormat'] = $modx->quote($scriptProperties['axisFormat']);
$scriptProperties['defaultView'] = $modx->quote($scriptProperties['defaultView']);

if (!$scriptProperties['allowGuestEdit'] && !$modx->user->isAuthenticated($modx->context->get('key')))
	$scriptProperties['readOnly'] = true;

$_SESSION['mycalendar']['scriptProperties'] = $scriptProperties;

/** @var myCalendar $myCalendar */
$myCalendar = $modx->getService('mycalendar','myCalendar',MODX_CORE_PATH.'components/mycalendar/model/mycalendar/',$scriptProperties);
$myCalendar->initialize();

if (empty($tpl)) {$tpl = 'tpl.myCalendar';}
$output = "<div id='calendar'></div>\n<div class='event-modal' id='dialog'></div>\n";
if (!$scriptProperties['readOnly']) {
	$output .= <<<DLG
		<div class="event-modal" id="remove-dialog">
			<p>Вы уверены?</p>
		</div>\n
DLG;
}
return $output;
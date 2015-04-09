<?php

$properties = array();

$tmp = array(
	'allDaySlot' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	'axisFormat' => array(
		'type' => 'textfield',
		'value' => 'H:mm',
	),
	'defaultColor' => array(
		'type' => 'textfield',
		'value' => '#0070c0',
	),
	'defaultDuration'=> array(
		'type' => 'textfield',
		'value' => '01:00',
	),
	'defaultView'=> array(
		'type' => 'list',
		'options' => array(
			array('text' => 'month', 'value' => 'month'),
			array('text' => 'agendaWeek', 'value' => 'agendaWeek'),
			array('text' => 'agendaDay', 'value' => 'agendaDay'),
		),
		'value' => 'agendaWeek'
	),
	'fixedWeekCount' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'googleCalendars'=> array(
		'type' => 'textfield',
		'value' => '',
	),
	'googleClass'=> array(
		'type' => 'textfield',
		'value' => 'google-calendar',
	),
	'height'=> array(
		'type' => 'numberfield',
		'value' => 700,
	),
	'hiddenDays'=> array(
		'type' => 'textfield',
		'value' => '',
	),
	'maxTime' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'minTime' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'readOnly' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'showWeekNumber' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'showWeekends' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	'tpl' => array(
		'type' => 'textfield',
		'value' => 'tpl.myCalendar',
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			'desc' => PKG_NAME_LOWER . '_prop_' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;
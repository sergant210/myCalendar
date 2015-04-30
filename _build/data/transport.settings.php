<?php

$settings = array();

$tmp = array(
	'google_calendar_api_key' => array(
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'mycalendar.main',
	),
	'default_css' => array(
		'xtype' => 'textfield',
		'value' => 'default.min.css',
		'area' => 'mycalendar.main',
	),
	'default_js' => array(
		'xtype' => 'textfield',
		'value' => 'default.js',
		'area' => 'mycalendar.main',
	),

);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'mycalendar.' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;

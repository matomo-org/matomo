<?php

Piwik_AddAction('Menu', 'Piwik_BuildMenu');

static $widgets = array();

function Piwik_GetListWidgets()
{
	global $widgets;
	return $widgets;
}

function Piwik_AddWidget( $pluginName, $controllerMethodToCall, $widgetTitle )
{
	global $widgets;	
	// get the plugin name from controller
	$widgets[$pluginName][] = array( $widgetTitle, $controllerMethodToCall );
}

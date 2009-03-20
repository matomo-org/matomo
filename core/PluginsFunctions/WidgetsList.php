<?php
function Piwik_GetWidgetsList()
{
	return Piwik_WidgetsList::get();
}

function Piwik_AddWidget( $pluginName, $controllerMethodToCall, $widgetTitle )
{
	Piwik_WidgetsList::add($pluginName, $controllerMethodToCall, $widgetTitle);
}

class Piwik_WidgetsList
{
	static protected $widgets = null;
	
	static function get()
	{
		if(is_null(self::$widgets)) {
			self::$widgets = array();
			Piwik_PostEvent('WidgetsList.add');
		}
		return self::$widgets;
	}
	
	static function add($pluginName, $controllerMethodToCall, $widgetTitle)
	{
		self::$widgets[$pluginName][] = array( $widgetTitle, $controllerMethodToCall );
	}
}

<?php
function Piwik_GetWidgetsList()
{
	return Piwik_WidgetsList::get();
}

function Piwik_AddWidget( $widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
{
	Piwik_WidgetsList::add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters);
}

class Piwik_WidgetsList
{
	static protected $widgets = null;
	
	static function get()
	{
		Piwik_PostEvent('WidgetsList.add');
		return self::$widgets;
	}
	
	static function add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters)
	{
		$widgetCategory = Piwik_Translate($widgetCategory);
		$widgetName = Piwik_Translate($widgetName);
		$widgetUniqueId = 'widget' . $controllerName . $controllerAction;
		self::$widgets[$widgetCategory][] = array( 
					'name' => $widgetName,
					'uniqueId' => $widgetUniqueId,
					'parameters' => array (	'module' => $controllerName,
											'action' => $controllerAction
										) + $customParameters
									);
	}
}

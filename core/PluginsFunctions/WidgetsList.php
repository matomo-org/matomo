<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package PluginsFunctions
 */

/**
 * @package PluginsFunctions
 */
class Piwik_WidgetsList
{
	static protected $widgets = null;
	static protected $hookCalled = false;
	
	static function get()
	{
		if(!self::$hookCalled)
		{
			self::$hookCalled = true;
			Piwik_PostEvent('WidgetsList.add');
		}
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
	
	static function isDefined($controllerName, $controllerAction)
	{
		$widgetsList = self::get();
		foreach($widgetsList as $widgetCategory => $widgets) 
		{
			foreach($widgets as $widget)
			{
    			if($widget['parameters']['module'] == $controllerName
    				&& $widget['parameters']['action'] == $controllerAction)
    			{
    				return true;
    			}
			}
		}
		return false;
	}
}

function Piwik_GetWidgetsList()
{
	return Piwik_WidgetsList::get();
}

function Piwik_AddWidget( $widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
{
	Piwik_WidgetsList::add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters);
}

function Piwik_IsWidgetDefined($controllerName, $controllerAction)
{
	return Piwik_WidgetsList::isDefined($controllerName, $controllerAction);
}

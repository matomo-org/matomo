<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
	/**
	 * List of widgets
	 *
	 * @var array
	 */
	static protected $widgets = null;

	/**
	 * Indicated whether the hook was posted or not
	 *
	 * @var bool
	 */
	static protected $hookCalled = false;

	/**
	 * Returns all available widgets
	 * The event WidgetsList.add is used to create the list
	 *
	 * @return array
	 */
	static public function get()
	{
		if(!self::$hookCalled)
		{
			self::$hookCalled = true;
			Piwik_PostEvent('WidgetsList.add');
		}
		return self::$widgets;
	}

	/**
	 * Adds an widget to the list
	 *
	 * @param string  $widgetCategory
	 * @param string  $widgetName
	 * @param string  $controllerName
	 * @param string  $controllerAction
	 * @param array   $customParameters
	 */
	static public function add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters)
	{
		$widgetCategory = Piwik_Translate($widgetCategory);
		$widgetName = Piwik_Translate($widgetName);
		$widgetUniqueId = 'widget' . $controllerName . $controllerAction;
		foreach($customParameters as $name => $value)
		{
			if (is_array($value))
			{
				// use 'Array' for backward compatibility;
				// could we switch to using $value[0]?
				$value = 'Array';
			}
			$widgetUniqueId .= $name . $value;
		}
		self::$widgets[$widgetCategory][] = array( 
					'name' => $widgetName,
					'uniqueId' => $widgetUniqueId,
					'parameters' => array (	'module' => $controllerName,
											'action' => $controllerAction
										) + $customParameters
									);
	}

	/**
	 * Checks if the widget with the given parameters exists in der widget list
	 *
	 * @param string  $controllerName
	 * @param string  $controllerAction
	 * @return bool
	 */
	static public function isDefined($controllerName, $controllerAction)
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

/**
 * Returns all available widgets
 *
 * @see Piwik_WidgetsList::get
 *
 * @return array
 */
function Piwik_GetWidgetsList()
{
	return Piwik_WidgetsList::get();
}

/**
 * Adds an widget to the list
 *
 * @see Piwik_WidgetsList::add
 *
 * @param string  $widgetCategory
 * @param string  $widgetName
 * @param string  $controllerName
 * @param string  $controllerAction
 * @param array   $customParameters
 */
function Piwik_AddWidget( $widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
{
	Piwik_WidgetsList::add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters);
}

/**
 * Checks if the widget with the given parameters exists in der widget list
 *
 * @see Piwik_WidgetsList::isDefined
 *
 * @param string  $controllerName
 * @param string  $controllerAction
 * @return bool
 */
function Piwik_IsWidgetDefined($controllerName, $controllerAction)
{
	return Piwik_WidgetsList::isDefined($controllerName, $controllerAction);
}

<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Alerts
 */

namespace Piwik\Plugins\CustomAlerts;

use Piwik;
use Piwik\Db;
use Piwik\Menu\MenuTop;
use Piwik\ScheduledTask;
use Piwik\ScheduledTime;

/**
 *
 * @package Piwik_Alerts
 */
class CustomAlerts extends \Piwik\Plugin
{

	public function getListHooksRegistered()
	{
		return array(
		    'Menu.Top.addItems' => 'addTopMenu',
		    'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
		    'AssetManager.getJavaScriptFiles' => 'getJsFiles',
		    'AssetManager.getStylesheetFiles' => 'getCssFiles',
		);
	}

	public function getJsFiles(&$jsFiles)
	{
		$jsFiles[] = "plugins/CustomAlerts/javascripts/ui.dropdownchecklist.js";
	}

	public function getCssFiles(&$cssFiles)
	{
		$cssFiles[] = "plugins/CustomAlerts/stylesheets/ui.dropdownchecklist.css";
	}

	public function install()
	{
		Model::install();
	}

	public function uninstall()
	{
		Model::uninstall();
	}

	public function addTopMenu()
	{
        MenuTop::addEntry("Alerts", array("module" => "CustomAlerts", "action" => "index"), true, 9);
	}

	public function getScheduledTasks(&$tasks)
	{
		$tasks[] = new ScheduledTask(
		    'Piwik\Plugins\CustomAlerts\Processor',
		    'processAlerts',
            'day',
		    ScheduledTime::factory('daily')
		);

        $tasks[] = new ScheduledTask(
            'Piwik\Plugins\CustomAlerts\Notifier',
            'sendNewAlerts',
            'day',
            ScheduledTime::factory('daily')
        );

		$tasks[] = new ScheduledTask(
            'Piwik\Plugins\CustomAlerts\Processor',
		    'processAlerts',
            'week',
            ScheduledTime::factory('weekly')
		);

        $tasks[] = new ScheduledTask(
            'Piwik\Plugins\CustomAlerts\Notifier',
            'sendNewAlerts',
            'week',
            ScheduledTime::factory('weekly')
        );

		$tasks[] = new ScheduledTask(
            'Piwik\Plugins\CustomAlerts\Processor',
		    'processAlerts',
            'month',
            ScheduledTime::factory('monthly')
		);

        $tasks[] = new ScheduledTask(
            'Piwik\Plugins\CustomAlerts\Notifier',
            'sendNewAlerts',
            'month',
            ScheduledTime::factory('monthly')
        );
	}
}
?>

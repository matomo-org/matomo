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
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Exception;
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
		$tableAlert = "CREATE TABLE " . Common::prefixTable('alert') . " (
			`idalert` INT NOT NULL PRIMARY KEY ,
			`name` VARCHAR(100) NOT NULL ,
			`login` VARCHAR(100) NOT NULL ,
			`period` VARCHAR(5) NOT NULL ,
			`report` VARCHAR(150) NOT NULL ,
			`report_condition` VARCHAR(50) ,
			`report_matched` VARCHAR(255) ,
			`metric` VARCHAR(150) NOT NULL ,
			`metric_condition` VARCHAR(50) NOT NULL ,
			`metric_matched` FLOAT NOT NULL ,
			`enable_mail` BOOLEAN NOT NULL ,
			`deleted` BOOLEAN NOT NULL
		) DEFAULT CHARSET=utf8 ;";

		$tableAlertSite = "CREATE TABLE " . Common::prefixTable('alert_site') . "(
			`idalert` INT( 11 ) NOT NULL ,
			`idsite` INT( 11 ) NOT NULL ,
			PRIMARY KEY ( idalert, idsite )
		) DEFAULT CHARSET=utf8 ;";

		$tableAlertLog = "CREATE TABLE " . Common::prefixTable('alert_log') . " (
			`idalert` INT( 11 ) NOT NULL ,
			`idsite` INT( 11 ) NOT NULL ,
			`ts_triggered` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			KEY `ts_triggered` (`ts_triggered`)
		)";

		try {
			Db::exec($tableAlert);
            Db::exec($tableAlertLog);
            Db::exec($tableAlertSite);
		} catch (Exception $e) {
			// mysql code error 1050:table already exists
			// see bug #153 http://dev.piwik.org/trac/ticket/153
			if (!Db::get()->isErrNo($e, '1050')) {
				throw $e;
			}
		}
	}

	public function uninstall()
	{
		$tables = array('alert', 'alert_log', 'alert_site');
		foreach ($tables as $table) {
			$sql = "DROP TABLE " . Common::prefixTable($table);
			Db::exec($sql);
		}
	}

	public function addTopMenu()
	{
        MenuTop::addEntry("Alerts", array("module" => "CustomAlerts", "action" => "index"), true, 9);
	}

	public function getScheduledTasks(&$tasks)
	{
		$tasks[] = new ScheduledTask(
				'Piwik\Plugins\CustomAlerts\Processor',
				'processDailyAlerts',
                null,
				ScheduledTime::factory('daily')
		);

		$tasks[] = new ScheduledTask(
                'Piwik\Plugins\CustomAlerts\Processor',
				'processWeeklyAlerts',
                null,
                ScheduledTime::factory('weekly')
		);

		$tasks[] = new ScheduledTask(
                'Piwik\Plugins\CustomAlerts\Processor',
				'processMonthlyAlerts',
                null,
                ScheduledTime::factory('monthly')
		);
	}
}
?>

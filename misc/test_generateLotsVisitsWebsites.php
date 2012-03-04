<?php
define('PIWIK_INCLUDE_PATH', realpath( dirname(__FILE__)."/.." ));
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";
require_once PIWIK_INCLUDE_PATH . "/libs/PiwikTracker/PiwikTracker.php";

Piwik_FrontController::getInstance()->init();

if(!Piwik_Common::isPhpCliMode()) { die("ERROR: Must be executed in CLI"); }

$process = new Piwik_StressTests_CopyLogs;
$process->init();
$process->run();
//$process->delete();

class Piwik_StressTests_CopyLogs
{
	function init()
	{
		$config = Piwik_Config::getInstance();
		$config->log['log_only_when_debug_parameter'] = 0;
		$config->log['logger_message'] = array("logger_message" => "screen");
		Piwik::createLogObject();
	}
	
	function run()
	{
		// Copy all visits in date range into TODAY
        $startDate = '2011-08-12';
        $endDate = '2011-08-12';
		
        $this->log("Starting...");
		$db = Zend_Registry::get('db');
		
		$initial = $this->getVisitsToday();
		$this->log(" Visits today so far: " . $initial);
		$initialActions = $this->getActionsToday();
		$this->log(" Actions today: " . $initialActions);
		$initialPurchasedItems = $this->getConversionItemsToday();
		$this->log(" Purchased items today: " . $initialPurchasedItems);
		$initialConversions = $this->getConversionsToday();
		$this->log(" Conversions today: " . $initialConversions);
		
		$this->log(" Now copying visits between '$startDate' and '$endDate'...");
		$sql = "INSERT INTO ". Piwik_Common::prefixTable('log_visit')." (`idsite`, `idvisitor`, `visitor_localtime`, `visitor_returning`, `visitor_count_visits`, `visit_first_action_time`, `visit_last_action_time`, `visit_exit_idaction_url`, `visit_exit_idaction_name`, `visit_entry_idaction_url`, `visit_entry_idaction_name`, `visit_total_actions`, `visit_total_time`, `visit_goal_converted`, `visit_goal_buyer`, `referer_type`, `referer_name`, `referer_url`, `referer_keyword`, `config_id`, `config_os`, `config_browser_name`, `config_browser_version`, `config_resolution`, `config_pdf`, `config_flash`, `config_java`, `config_director`, `config_quicktime`, `config_realplayer`, `config_windowsmedia`, `config_gears`, `config_silverlight`, `config_cookie`, `location_ip`, `location_browser_lang`, `location_country`, `location_continent`, `location_provider`, `custom_var_k1`, `custom_var_v1`, `custom_var_k2`, `custom_var_v2`, `custom_var_k3`, `custom_var_v3`, `custom_var_k4`, `custom_var_v4`, `custom_var_k5`, `custom_var_v5`, `visitor_days_since_last`, `visitor_days_since_order`, `visitor_days_since_first`)
		SELECT `idsite`, `idvisitor`, `visitor_localtime`, `visitor_returning`, `visitor_count_visits`, CONCAT(CURRENT_DATE()  , \" \",  FLOOR(RAND()*24) , \":\",FLOOR(RAND()*60),\":\",FLOOR(RAND()*60)), CONCAT(CURRENT_DATE()  , \" \",  FLOOR(RAND()*24) , \":\",FLOOR(RAND()*60),\":\",FLOOR(RAND()*60)), `visit_exit_idaction_url`, `visit_exit_idaction_name`, `visit_entry_idaction_url`, `visit_entry_idaction_name`, `visit_total_actions`, `visit_total_time`, `visit_goal_converted`, `visit_goal_buyer`, `referer_type`, `referer_name`, `referer_url`, `referer_keyword`, `config_id`, `config_os`, `config_browser_name`, `config_browser_version`, `config_resolution`, `config_pdf`, `config_flash`, `config_java`, `config_director`, `config_quicktime`, `config_realplayer`, `config_windowsmedia`, `config_gears`, `config_silverlight`, `config_cookie`, `location_ip`, `location_browser_lang`, `location_country`, `location_continent`, `location_provider`, `custom_var_k1`, `custom_var_v1`, `custom_var_k2`, `custom_var_v2`, `custom_var_k3`, `custom_var_v3`, `custom_var_k4`, `custom_var_v4`, `custom_var_k5`, `custom_var_v5`, `visitor_days_since_last`, `visitor_days_since_order`, `visitor_days_since_first` 
		FROM `". Piwik_Common::prefixTable('log_visit')."` 
		WHERE idsite >= 1 AND date(visit_last_action_time) between '$startDate' and '$endDate' ;";
		$result = $db->query($sql);
		
		$this->log(" Copying actions...");
		$sql = "INSERT INTO ". Piwik_Common::prefixTable('log_link_visit_action')." (`idsite`, `idvisitor`, `server_time`, `idvisit`, `idaction_url`, `idaction_url_ref`, `idaction_name`, `idaction_name_ref`, `time_spent_ref_action`, `custom_var_k1`, `custom_var_v1`, `custom_var_k2`, `custom_var_v2`, `custom_var_k3`, `custom_var_v3`, `custom_var_k4`, `custom_var_v4`, `custom_var_k5`, `custom_var_v5`)
		SELECT `idsite`, `idvisitor`, CONCAT(CURRENT_DATE()  , \" \",  FLOOR(RAND()*24) , \":\",FLOOR(RAND()*60),\":\",FLOOR(RAND()*60)), `idvisit`, `idaction_url`, `idaction_url_ref`, `idaction_name`, `idaction_name_ref`, `time_spent_ref_action`, `custom_var_k1`, `custom_var_v1`, `custom_var_k2`, `custom_var_v2`, `custom_var_k3`, `custom_var_v3`, `custom_var_k4`, `custom_var_v4`, `custom_var_k5`, `custom_var_v5` 
		FROM `". Piwik_Common::prefixTable('log_link_visit_action')."` 
		WHERE idsite >= 1 AND date(server_time) between '$startDate' and '$endDate' 
		
		;"; // LIMIT 1000000
		$result = $db->query($sql);
		
		$this->log(" Copying conversions...");
		$sql = "INSERT IGNORE  INTO `". Piwik_Common::prefixTable('log_conversion')."` (`idvisit`, `idsite`, `visitor_days_since_first`, `visitor_days_since_order`, `visitor_count_visits`, `idvisitor`, `server_time`, `idaction_url`, `idlink_va`, `referer_visit_server_date`, `referer_type`, `referer_name`, `referer_keyword`, `visitor_returning`, `location_country`, `location_continent`, `url`, `idgoal`, `revenue`, `buster`, `idorder`, `custom_var_k1`, `custom_var_v1`, `custom_var_k2`, `custom_var_v2`, `custom_var_k3`, `custom_var_v3`, `custom_var_k4`, `custom_var_v4`, `custom_var_k5`, `custom_var_v5`, `items`, `revenue_subtotal`, `revenue_tax`, `revenue_shipping`, `revenue_discount`)
		SELECT `idvisit`, `idsite`, `visitor_days_since_first`, `visitor_days_since_order`, `visitor_count_visits`, `idvisitor`, CONCAT(CURRENT_DATE()  , \" \",  FLOOR(RAND()*24) , \":\",FLOOR(RAND()*60),\":\",FLOOR(RAND()*60)), `idaction_url`, `idlink_va`, `referer_visit_server_date`, `referer_type`, `referer_name`, `referer_keyword`, `visitor_returning`, `location_country`, `location_continent`, `url`, `idgoal`, `revenue`, FLOOR(`buster` * RAND()), CONCAT(`idorder`,SUBSTRING(MD5(RAND()) FROM 1 FOR 9))  , `custom_var_k1`, `custom_var_v1`, `custom_var_k2`, `custom_var_v2`, `custom_var_k3`, `custom_var_v3`, `custom_var_k4`, `custom_var_v4`, `custom_var_k5`, `custom_var_v5`, `items`, `revenue_subtotal`, `revenue_tax`, `revenue_shipping`, `revenue_discount`
		FROM `". Piwik_Common::prefixTable('log_conversion')."` 
		WHERE idsite >= 1 AND date(server_time) between '$startDate' and '$endDate' ;";
		$result = $db->query($sql);
		
		$this->log(" Copying purchased items...");
		$sql = "INSERT INTO `". Piwik_Common::prefixTable('log_conversion_item')."` (`idsite`, `idvisitor`, `server_time`, `idvisit`, `idorder`, `idaction_sku`, `idaction_name`, `idaction_category`, `price`, `quantity`, `deleted`) 
		SELECT `idsite`, `idvisitor`, CONCAT(CURRENT_DATE()  , \" \",  TIME(`server_time`)), `idvisit`, CONCAT(`idorder`,SUBSTRING(MD5(RAND()) FROM 1 FOR 9)) , `idaction_sku`, `idaction_name`, `idaction_category`, `price`, `quantity`, `deleted`
		FROM `". Piwik_Common::prefixTable('log_conversion_item')."` 
		WHERE idsite >= 1 AND date(server_time) between '$startDate' and '$endDate' ;";
		$result = $db->query($sql);
		
		$now = $this->getVisitsToday();
		$actions = $this->getActionsToday();
		$purchasedItems = $this->getConversionItemsToday();
		$conversions = $this->getConversionsToday();

		$this->log(" -------------------------------------");
		$this->log(" Today visits after import: " . $now);
		$this->log(" Actions: " . $actions);
		$this->log(" Purchased items: " . $purchasedItems);
		$this->log(" Conversions: " . $conversions);
		$this->log(" - New visits created: " . ($now-$initial));
		$this->log(" - Actions created: " . ($actions-$initialActions));
		$this->log(" - New conversions created: " . ($conversions-$initialConversions));
		$this->log(" - New purchased items created: " . ($purchasedItems-$initialPurchasedItems));
        $this->log("done");
	}
	
	function delete()
	{
		$this->log("Deleting logs for today...");
		$db = Zend_Registry::get('db');
		$sql = "DELETE FROM ". Piwik_Common::prefixTable('log_visit')." 
				WHERE date(visit_last_action_time) = CURRENT_DATE();";
		$db->query($sql);
		foreach(array('log_link_visit_action', 'log_conversion', 'log_conversion_item') as $table)
		{
			$sql = "DELETE FROM ".Piwik_Common::prefixTable($table)."
		 		WHERE date(server_time) = CURRENT_DATE();";
			$db->query($sql);
		}
		$sql = "OPTIMIZE TABLE ". Piwik_Common::prefixTable('log_link_visit_action').", ". Piwik_Common::prefixTable('log_conversion').", ". Piwik_Common::prefixTable('log_conversion_item').", ". Piwik_Common::prefixTable('log_visit')."";
		$db->query($sql);
		$this->log("done");
	}
	
	function log($m)
	{
		Piwik::log($m);
	}
	function getVisitsToday()
	{
		$sql = "SELECT count(*) FROM `". Piwik_Common::prefixTable('log_visit')."` WHERE idsite >= 1 AND DATE(`visit_last_action_time`) = CURRENT_DATE;";
		return Zend_Registry::get('db')->fetchOne($sql);
	}
	function getConversionItemsToday($table = 'log_conversion_item')
	{
		$sql = "SELECT count(*) FROM `".Piwik_Common::prefixTable($table)."` WHERE idsite >= 1 AND DATE(`server_time`) = CURRENT_DATE;";
		return Zend_Registry::get('db')->fetchOne($sql);
	}
	function getConversionsToday()
	{
		return $this->getConversionItemsToday($table = "log_conversion");
	}
	function getActionsToday()
	{
		$sql = "SELECT count(*) FROM `". Piwik_Common::prefixTable('log_link_visit_action')."` WHERE idsite >= 1 AND DATE(`server_time`) = CURRENT_DATE;";
		return Zend_Registry::get('db')->fetchOne($sql);
	}
}

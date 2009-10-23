<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

// no direct access
defined('PIWIK_INCLUDE_PATH') or die;

/**
 * @see core/Translate.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Translate.php';

/**
 * Main piwik helper class.
 * Contains static functions you can call from the plugins.
 * 
 * @package Piwik
 */
class Piwik
{
	const CLASSES_PREFIX = "Piwik_";
	
	public static $idPeriods =  array(
			'day'	=> 1,
			'week'	=> 2,
			'month'	=> 3,
			'year'	=> 4,
		);
	
	/**
	 * Checks that the directories Piwik needs write access are actually writable
	 * Displays a nice error page if permissions are missing on some directories
	 */
	static public function checkDirectoriesWritableOrDie( $directoriesToCheck = null )
	{
		$resultCheck = Piwik::checkDirectoriesWritable( $directoriesToCheck );
		if( array_search(false, $resultCheck) !== false )
		{ 
			$directoryList = '';
			foreach($resultCheck as $dir => $bool)
			{
				$realpath = Piwik_Common::realpath($dir);
				if(!empty($realpath) && $bool === false)
				{
					$directoryList .= "<code>chmod 777 $realpath</code><br>";
				}
			}
			$directoryList .= '';
			$directoryMessage = "<p><b>Piwik couldn't write to some directories</b>.</p> <p>Try to Execute the following commands on your Linux server:</P>";
			$directoryMessage .= $directoryList;
			$directoryMessage .= "<p>If this doesn't work, you can try to create the directories with your FTP software, and set the CHMOD to 777 (with your FTP software, right click on the directories, permissions).";
			$directoryMessage .= "<p>After applying the modifications, you can <a href='index.php'>refresh the page</a>.";
			$directoryMessage .= "<p>If you need more help, try <a href='misc/redirectToUrl.php?url=http://piwik.org'>Piwik.org</a>.";
			
			Piwik_ExitWithMessage($directoryMessage, false, true);
		}
	}
	
	/**
	 * Checks if directories are writable and create them if they do not exist.
	 * 
	 * @param array $directoriesToCheck array of directories to check - if not given default Piwik directories that needs write permission are checked
	 * @return array direcory name => true|false (is writable)
	 */
	static public function checkDirectoriesWritable($directoriesToCheck = null)
	{
		if( $directoriesToCheck == null )		
		{
			$directoriesToCheck = array(
				'/config',
				'/tmp',
				'/tmp/templates_c',
				'/tmp/cache',
				'/tmp/latest',
			); 
		}
		
		$resultCheck = array();
		foreach($directoriesToCheck as $directoryToCheck)
		{
			if( !preg_match('/^'.preg_quote(PIWIK_USER_PATH, '/').'/', $directoryToCheck) )
			{
				$directoryToCheck = PIWIK_USER_PATH . $directoryToCheck;
			}
			
			if(!file_exists($directoryToCheck))
			{
				Piwik_Common::mkdir($directoryToCheck, 0755, false);
			}
			
			$directory = Piwik_Common::realpath($directoryToCheck);
			$resultCheck[$directory] = false;
			if($directory !== false // realpath() returns FALSE on failure
				&& is_writable($directoryToCheck))
			{
				$resultCheck[$directory] = true;
			}
		}
		return $resultCheck;
	}
	
	/**
	 * Returns the Javascript code to be inserted on every page to track
	 *
	 * @param int $idSite
	 * @param string $piwikUrl http://path/to/piwik/directory/ 
	 * @param string $actionName
	 * @return string
	 */
	static public function getJavascriptCode($idSite, $piwikUrl, $actionName = "''")
	{	
		$jsTag = file_get_contents( PIWIK_INCLUDE_PATH . "/core/Tracker/javascriptTag.tpl");
		$jsTag = nl2br(htmlentities($jsTag));
		$piwikUrl = preg_match('~^(http|https)://(.*)$~', $piwikUrl, $matches);
		$piwikUrl = $matches[2];
		$jsTag = str_replace('{$actionName}', $actionName, $jsTag);
		$jsTag = str_replace('{$idSite}', $idSite, $jsTag);
		$jsTag = str_replace('{$piwikUrl}', $piwikUrl, $jsTag);
		$jsTag = str_replace('{$hrefTitle}', Piwik::getRandomTitle(), $jsTag);
		return $jsTag;
	}

	/**
	 * Set maximum script execution time.
	 *
	 * @param int max execution time in seconds (0 = no limit)
	 */
	static public function setMaxExecutionTime($executionTime)
	{
		// in the event one or the other is disabled...
		@ini_set('max_execution_time', $executionTime);
		@set_time_limit($executionTime);
	}

	static public function getMemoryLimitValue()
	{
		if($memory = ini_get('memory_limit'))
		{
			return substr($memory, 0, strlen($memory) - 1);
		}
		return false;
	}
	
	static public function setMemoryLimit($minimumMemoryLimit)
	{
		$currentValue = self::getMemoryLimitValue();
		if( ($currentValue === false
			|| $currentValue < $minimumMemoryLimit )
			&& @ini_set('memory_limit', $minimumMemoryLimit.'M'))
		{
			return true;
		}
		return false;
	}
	
	static public function raiseMemoryLimitIfNecessary()
	{
		$minimumMemoryLimit = Zend_Registry::get('config')->General->minimum_memory_limit;
		$memoryLimit = self::getMemoryLimitValue();
		if($memoryLimit === false
			|| $memoryLimit < $minimumMemoryLimit)
		{
			return self::setMemoryLimit($minimumMemoryLimit);
		}
		
		return false;
	}
	
	static public function log($message = '')
	{
		Zend_Registry::get('logger_message')->logEvent($message);
		Zend_Registry::get('logger_message')->logEvent( "<br>" . PHP_EOL);
	}
	
	
	static public function error($message = '')
	{
		trigger_error($message, E_USER_ERROR);
	}
	
	/**
	 * Display the message in a nice red font with a nice icon
	 * ... and dies
	 */
	static public function exitWithErrorMessage( $message )
	{
		$output = "<style>a{color:red;}</style>\n".
			"<div style='color:red;font-family:Georgia;font-size:120%'>".
			"<p><img src='themes/default/images/error_medium.png' style='vertical-align:middle; float:left;padding:20 20 20 20'>".
			$message.
			"</p></div>";
		print(Piwik_Log_Formatter_ScreenFormatter::getFormattedString($output));
		exit;
	}
	
	/**
	 * Computes the division of i1 by i2. If either i1 or i2 are not number, or if i2 has a value of zero
	 * we return 0 to avoid the division by zero.
	 *
	 * @param numeric $i1
	 * @param numeric $i2
	 * @return numeric The result of the division or zero 
	 */
	static public function secureDiv( $i1, $i2 )
	{
		if ( is_numeric($i1) && is_numeric($i2) && floatval($i2) != 0)
		{ 
			return $i1 / $i2;
		}   
		return 0;
	}
	static public function getQueryCount()
	{
		$profiler = Zend_Registry::get('db')->getProfiler();
		return $profiler->getTotalNumQueries();
	}
	static public function getDbElapsedSecs()
	{
		$profiler = Zend_Registry::get('db')->getProfiler();
		return $profiler->getTotalElapsedSecs();
	}
	static public function printQueryCount()
	{
		$totalTime = self::getDbElapsedSecs();
		$queryCount = self::getQueryCount();
		Piwik::log("Total queries = $queryCount (total sql time = ".round($totalTime,2)."s)");
	}
	
	static public function printSqlProfilingReportTracker( $db = null )
	{
		if(!function_exists('maxSumMsFirst'))
		{
			function maxSumMsFirst($a,$b)
			{
				return $a['sum_time_ms'] < $b['sum_time_ms'];
			}
		}
		
		if(is_null($db))
		{
			$db = Piwik_Tracker::getDatabase();
		}
		$tableName = Piwik_Common::prefixTable('log_profiling');
		
		$all = $db->fetchAll('SELECT * FROM '.$tableName );
		if($all === false) 
		{
			return;
		}
		uasort($all, 'maxSumMsFirst');
		
		$infoIndexedByQuery = array();
		foreach($all as $infoQuery)
		{
			$query = $infoQuery['query'];
			$count = $infoQuery['count'];
			$sum_time_ms = $infoQuery['sum_time_ms'];
			$infoIndexedByQuery[$query] = array('count' => $count, 'sumTimeMs' => $sum_time_ms);
		}		
		Piwik::getSqlProfilingQueryBreakdownOutput($infoIndexedByQuery);
	}

	/**
	 * Outputs SQL Profiling reports 
	 * It is automatically called when enabling the SQL profiling in the config file enable_sql_profiler
	 *
	 */
	static function printSqlProfilingReportZend()
	{
		$profiler = Zend_Registry::get('db')->getProfiler();
		
		if(!$profiler->getEnabled())
		{
			throw new Exception("To display the profiler you should enable enable_sql_profiler on your config/config.ini.php file");
		}
		
		$infoIndexedByQuery = array();
		foreach($profiler->getQueryProfiles() as $query)
		{
			if(isset($infoIndexedByQuery[$query->getQuery()]))
			{
				$existing =  $infoIndexedByQuery[$query->getQuery()];
			}
			else
			{
				$existing = array( 'count' => 0, 'sumTimeMs' => 0);
			}
			$new = array( 'count' => $existing['count'] + 1,
							'sumTimeMs' =>  $existing['count'] + $query->getElapsedSecs() * 1000);
			$infoIndexedByQuery[$query->getQuery()] = $new;
		}

		if(!function_exists('sortTimeDesc'))
		{
			function sortTimeDesc($a,$b)
			{
				return $a['sumTimeMs'] < $b['sumTimeMs'];
			}
		}
		uasort( $infoIndexedByQuery, 'sortTimeDesc');
		
		Piwik::log('<hr><b>SQL Profiler</b>');
		Piwik::log('<hr><b>Summary</b>');
		$totalTime	= $profiler->getTotalElapsedSecs();
		$queryCount   = $profiler->getTotalNumQueries();
		$longestTime  = 0;
		$longestQuery = null;
		foreach ($profiler->getQueryProfiles() as $query) {
			if ($query->getElapsedSecs() > $longestTime) {
				$longestTime  = $query->getElapsedSecs();
				$longestQuery = $query->getQuery();
			}
		}
		$str = 'Executed ' . $queryCount . ' queries in ' . round($totalTime,3) . ' seconds' . "\n";
		$str .= '(Average query length: ' . round($totalTime / $queryCount,3) . ' seconds)' . "\n";
		$str .= '<br>Queries per second: ' . round($queryCount / $totalTime,1) . "\n";
		$str .= '<br>Longest query length: ' . round($longestTime,3) . " seconds (<code>$longestQuery</code>) \n";
		Piwik::log($str);
		Piwik::getSqlProfilingQueryBreakdownOutput($infoIndexedByQuery);
	}
	
	static private function getSqlProfilingQueryBreakdownOutput( $infoIndexedByQuery )
	{
		Piwik::log('<hr><b>Breakdown by query</b>');
		$output = '';
		foreach($infoIndexedByQuery as $query => $queryInfo) 
		{
			$timeMs = round($queryInfo['sumTimeMs'],1);
			$count = $queryInfo['count'];
			$avgTimeString = '';
			if($count > 1) 
			{
				$avgTimeMs = $timeMs / $count;
				$avgTimeString = " (average = <b>". round($avgTimeMs,1) . "ms</b>)"; 
			}
			$query = preg_replace('/([\t\n\r ]+)/', ' ', $query);
			$output .= "Executed <b>$count</b> time". ($count==1?'':'s') ." in <b>".$timeMs."ms</b> $avgTimeString <pre>\t$query</pre>";
		}
		Piwik::log($output);
	}
	
	static public function printTimer()
	{
		echo Zend_Registry::get('timer');
	}

	static public function printMemoryLeak($prefix = '', $suffix = '<br>')
	{
		echo $prefix;
		echo Zend_Registry::get('timer')->getMemoryLeak();
		echo $suffix;
	}
	
	static public function printMemoryUsage( $prefixString = null )
	{
		$memory = false;
		if(function_exists('xdebug_memory_usage'))
		{
			$memory = xdebug_memory_usage();
		}
		elseif(function_exists('memory_get_usage'))
		{
			$memory = memory_get_usage();
		}
				
		if($memory !== false)
		{
			$usage = round( $memory / 1024 / 1024, 2);
			if(!is_null($prefixString))
			{
				Piwik::log($prefixString);
			}
			Piwik::log("Memory usage = $usage Mb");
		}
		else
		{
			Piwik::log("Memory usage function not found.");
		}
	}
	
	static public function getPrettySizeFromBytes($size)
	{
		$bytes = array('','K','M','G','T');
		foreach($bytes as $val) 
		{
			if($size > 1024)
			{
				$size = $size / 1024;
			}
			else
			{
				break;
			}
		}
		return round($size, 1)." ".$val;
	}

	/**
	 * Returns true if PHP was invoked as CGI or command-line interface (shell)
	 *
	 * @deprecated deprecated in 0.4.4
	 * @see Piwik_Common::isPhpCliMode()
	 * @return bool true if PHP invoked as a CGI or from CLI
	 */
	static public function isPhpCliMode()
	{
		return Piwik_Common::isPhpCliMode();
	}
	
	static public function getCurrency()
	{
		static $symbol = null;
		if(is_null($symbol))
		{
			$symbol = trim(Zend_Registry::get('config')->General->default_currency);
		}
		return $symbol;
	}

	static public function getPrettyMoney($value)
	{
		$symbol = self::getCurrency();
		return sprintf("$symbol%.2f", $value);
	}
	
	static public function getPercentageSafe($dividend, $divisor, $precision = 0)
	{
		if($divisor == 0)
		{
			return 0;
		}
		return round(100 * $dividend / $divisor, $precision);
	}
	
	static public function getPrettyTimeFromSeconds($numberOfSeconds)
	{
		$numberOfSeconds = (double)$numberOfSeconds;
		$days = floor($numberOfSeconds / 86400);
		
		$minusDays = $numberOfSeconds - $days * 86400;
		$hours = floor($minusDays / 3600);
		
		$minusDaysAndHours = $minusDays - $hours * 3600;
		$minutes = floor($minusDaysAndHours / 60 );
		
		$seconds = $minusDaysAndHours - $minutes * 60;
		
		if($days > 0)
		{
			return sprintf("%d days %d hours", $days, $hours);
		}
		elseif($hours > 0)
		{
			return sprintf("%d hours %d min", $hours, $minutes);
		}
		elseif($minutes > 0)
		{
			return sprintf("%d&nbsp;min&nbsp;%ds", $minutes, $seconds);		
		}
		else
		{
			return sprintf("%ds", $seconds);		
		}
	}
	
	static public function getRandomTitle()
	{
		$titles = array( 'Web analytics',
						'Analytics',
						'Web analytics api',
						'Open source analytics',
						'Open source web analytics',
						'Google Analytics alternative',
						'open source Google Analytics',
						'Free analytics',
						'Analytics software',
						'Free web analytics',
						'Free web statistics',
						'Web 2.0 analytics',
						'Statistics web 2.0',
				);
		$id = abs(intval(md5(substr(Piwik_Url::getCurrentHost(),7))));
		$title = $titles[ $id % count($titles)];
		return $title;
	}
	
	static public function getTableCreateSql( $tableName )
	{
		$tables = Piwik::getTablesCreateSql();
		
		if(!isset($tables[$tableName]))
		{
			throw new Exception("The table '$tableName' SQL creation code couldn't be found.");
		}
		
		return $tables[$tableName];
	}
	
	static public function getTablesCreateSql()
	{
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tables = array(
			'user' => "CREATE TABLE {$prefixTables}user (
						  login VARCHAR(100) NOT NULL,
						  password CHAR(32) NOT NULL,
						  alias VARCHAR(45) NOT NULL,
						  email VARCHAR(100) NOT NULL,
						  token_auth CHAR(32) NOT NULL,
						  date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
						  PRIMARY KEY(login),
						  UNIQUE INDEX uniq_keytoken(token_auth)
						)  DEFAULT CHARSET=utf8 
			",
			
			'access' => "CREATE TABLE {$prefixTables}access (
						  login VARCHAR(100) NOT NULL,
						  idsite INTEGER UNSIGNED NOT NULL,
						  access VARCHAR(10) NULL,
						  PRIMARY KEY(login, idsite)
						)  DEFAULT CHARSET=utf8 
			",
			
			'site' => "CREATE TABLE {$prefixTables}site (
						  idsite INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						  name VARCHAR(90) NOT NULL,
						  main_url VARCHAR(255) NOT NULL,
  						  ts_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
						  PRIMARY KEY(idsite)
						)  DEFAULT CHARSET=utf8 
			",
			
			'site_url' => "CREATE TABLE {$prefixTables}site_url (
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  url VARCHAR(255) NOT NULL,
							  PRIMARY KEY(idsite, url)
						)  DEFAULT CHARSET=utf8 
			",
			
			'goal' => "	CREATE TABLE `{$prefixTables}goal` (
							  `idsite` int(11) NOT NULL,
							  `idgoal` int(11) NOT NULL,
							  `name` varchar(50) NOT NULL,
							  `match_attribute` varchar(20) NOT NULL,
							  `pattern` varchar(255) NOT NULL,
							  `pattern_type` varchar(10) NOT NULL,
							  `case_sensitive` tinyint(4) NOT NULL,
							  `revenue` float NOT NULL,
							  `deleted` tinyint(4) NOT NULL default '0',
							  PRIMARY KEY  (`idsite`,`idgoal`)
							)  DEFAULT CHARSET=utf8 
			",
			
			'logger_message' => "CREATE TABLE {$prefixTables}logger_message (
									  idlogger_message INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  timestamp TIMESTAMP NULL,
									  message TEXT NULL,
									  PRIMARY KEY(idlogger_message)
									)  DEFAULT CHARSET=utf8 
			",
			
			'logger_api_call' => "CREATE TABLE {$prefixTables}logger_api_call (
									  idlogger_api_call INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  class_name VARCHAR(255) NULL,
									  method_name VARCHAR(255) NULL,
									  parameter_names_default_values TEXT NULL,
									  parameter_values TEXT NULL,
									  execution_time FLOAT NULL,
									  caller_ip BIGINT UNSIGNED NULL,
									  timestamp TIMESTAMP NULL,
									  returned_value TEXT NULL,
									  PRIMARY KEY(idlogger_api_call)
									)  DEFAULT CHARSET=utf8 
			",
			
			'logger_error' => "CREATE TABLE {$prefixTables}logger_error (
									  idlogger_error INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  timestamp TIMESTAMP NULL,
									  message TEXT NULL,
									  errno INTEGER UNSIGNED NULL,
									  errline INTEGER UNSIGNED NULL,
									  errfile VARCHAR(255) NULL,
									  backtrace TEXT NULL,
									  PRIMARY KEY(idlogger_error)
									) DEFAULT CHARSET=utf8 
			",
			
			'logger_exception' => "CREATE TABLE {$prefixTables}logger_exception (
									  idlogger_exception INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  timestamp TIMESTAMP NULL,
									  message TEXT NULL,
									  errno INTEGER UNSIGNED NULL,
									  errline INTEGER UNSIGNED NULL,
									  errfile VARCHAR(255) NULL,
									  backtrace TEXT NULL,
									  PRIMARY KEY(idlogger_exception)
									)  DEFAULT CHARSET=utf8 
			",
			
			
			'log_action' => "CREATE TABLE {$prefixTables}log_action (
									  idaction INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
									  name VARCHAR(255) NOT NULL,
									  hash INTEGER(10) UNSIGNED NOT NULL,
  									  type TINYINT UNSIGNED NULL,
									  PRIMARY KEY(idaction),
									  INDEX index_type_hash (type, hash)
						)  DEFAULT CHARSET=utf8 
			",
					
			'log_visit' => "CREATE TABLE {$prefixTables}log_visit (
							  idvisit INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  visitor_localtime TIME NOT NULL,
							  visitor_idcookie CHAR(32) NOT NULL,
							  visitor_returning TINYINT(1) NOT NULL,
							  visit_first_action_time DATETIME NOT NULL,
							  visit_last_action_time DATETIME NOT NULL,
							  visit_server_date DATE NOT NULL,
							  visit_exit_idaction_url INTEGER(11) NOT NULL,
							  visit_entry_idaction_url INTEGER(11) NOT NULL,
							  visit_total_actions SMALLINT(5) UNSIGNED NOT NULL,
							  visit_total_time SMALLINT(5) UNSIGNED NOT NULL,
							  visit_goal_converted TINYINT(1) NOT NULL,
							  referer_type INTEGER UNSIGNED NULL,
							  referer_name VARCHAR(70) NULL,
							  referer_url TEXT NOT NULL,
							  referer_keyword VARCHAR(255) NULL,
							  config_md5config CHAR(32) NOT NULL,
							  config_os CHAR(3) NOT NULL,
							  config_browser_name VARCHAR(10) NOT NULL,
							  config_browser_version VARCHAR(20) NOT NULL,
							  config_resolution VARCHAR(9) NOT NULL,
							  config_pdf TINYINT(1) NOT NULL,
							  config_flash TINYINT(1) NOT NULL,
							  config_java TINYINT(1) NOT NULL,
							  config_director TINYINT(1) NOT NULL,
							  config_quicktime TINYINT(1) NOT NULL,
							  config_realplayer TINYINT(1) NOT NULL,
							  config_windowsmedia TINYINT(1) NOT NULL,
							  config_gears TINYINT(1) NOT NULL,
							  config_silverlight TINYINT(1) NOT NULL,
							  config_cookie TINYINT(1) NOT NULL,
							  location_ip BIGINT UNSIGNED NOT NULL,
							  location_browser_lang VARCHAR(20) NOT NULL,
							  location_country CHAR(3) NOT NULL,
							  location_continent CHAR(3) NOT NULL,
							  PRIMARY KEY(idvisit),
							  INDEX index_idsite_date (idsite, visit_server_date)
							)  DEFAULT CHARSET=utf8 
			",		
			
			'log_conversion' => "CREATE TABLE `{$prefixTables}log_conversion` (
									  `idvisit` int(10) unsigned NOT NULL,
									  `idsite` int(10) unsigned NOT NULL,
									  `visitor_idcookie` char(32) NOT NULL,
									  `server_time` datetime NOT NULL,
									  `visit_server_date` date NOT NULL,
									  `idaction_url` int(11) default NULL,
									  `idlink_va` int(11) default NULL,
									  `referer_idvisit` int(10) unsigned default NULL,
									  `referer_visit_server_date` date default NULL,
									  `referer_type` int(10) unsigned default NULL,
									  `referer_name` varchar(70) default NULL,
									  `referer_keyword` varchar(255) default NULL,
									  `visitor_returning` tinyint(1) NOT NULL,
									  `location_country` char(3) NOT NULL,
									  `location_continent` char(3) NOT NULL,
									  `url` text NOT NULL,
									  `idgoal` int(10) unsigned NOT NULL,
									  `revenue` float default NULL,
									  PRIMARY KEY  (`idvisit`,`idgoal`),
									  KEY `index_idsite_date` (`idsite`,`visit_server_date`)
									) DEFAULT CHARSET=utf8 
			",
							
			'log_link_visit_action' => "CREATE TABLE {$prefixTables}log_link_visit_action (
											  idlink_va INTEGER(11) NOT NULL AUTO_INCREMENT,
											  idvisit INTEGER(10) UNSIGNED NOT NULL,
											  idaction_url INTEGER(10) UNSIGNED NOT NULL,
											  idaction_url_ref INTEGER(10) UNSIGNED NOT NULL,
											  idaction_name INTEGER(10) UNSIGNED,
											  time_spent_ref_action INTEGER(10) UNSIGNED NOT NULL,
											  PRIMARY KEY(idlink_va),
											  INDEX index_idvisit(idvisit)
											)  DEFAULT CHARSET=utf8 
			",
		
			'log_profiling' => "CREATE TABLE {$prefixTables}log_profiling (
								  query TEXT NOT NULL,
								  count INTEGER UNSIGNED NULL,
								  sum_time_ms FLOAT NULL,
								  UNIQUE INDEX query(query(100))
								)  DEFAULT CHARSET=utf8 
			",
			
			'option' => "CREATE TABLE `{$prefixTables}option` (
								option_name VARCHAR( 64 ) NOT NULL ,
								option_value LONGTEXT NOT NULL ,
								autoload TINYINT NOT NULL DEFAULT '1',
								PRIMARY KEY ( option_name )
								)  DEFAULT CHARSET=utf8 
			",
								
			'archive_numeric'	=> "CREATE TABLE {$prefixTables}archive_numeric (
									  idarchive INTEGER UNSIGNED NOT NULL,
									  name VARCHAR(255) NOT NULL,
									  idsite INTEGER UNSIGNED NULL,
									  date1 DATE NULL,
								  	  date2 DATE NULL,
									  period TINYINT UNSIGNED NULL,
								  	  ts_archived DATETIME NULL,
								  	  value FLOAT NULL,
									  PRIMARY KEY(idarchive, name),
									  KEY `index_all` (`idsite`,`date1`,`date2`,`name`,`ts_archived`)
									)  DEFAULT CHARSET=utf8 
			",
			'archive_blob'	=> "CREATE TABLE {$prefixTables}archive_blob (
									  idarchive INTEGER UNSIGNED NOT NULL,
									  name VARCHAR(255) NOT NULL,
									  idsite INTEGER UNSIGNED NULL,
									  date1 DATE NULL,
									  date2 DATE NULL,
									  period TINYINT UNSIGNED NULL,
									  ts_archived DATETIME NULL,
									  value MEDIUMBLOB NULL,
									  PRIMARY KEY(idarchive, name),
									  KEY `index_all` (`idsite`,`date1`,`date2`,`name`,`ts_archived`)
									)  DEFAULT CHARSET=utf8 
			",
		);
		return $tables;
	}
	
	static public function getCurrentUserLogin()
	{
		return Zend_Registry::get('access')->getLogin();
	}
	
	static public function getCurrentUserTokenAuth()
	{
		return Zend_Registry::get('access')->getTokenAuth();
	}
	
	/**
	 * Returns the plugin currently being used to display the page
	 *
	 * @return Piwik_Plugin
	 */
	static public function getCurrentPlugin()
	{
		return Piwik_PluginsManager::getInstance()->getLoadedPlugin(Piwik::getModule());
	}
	
	/**
	 * Returns true if the current user is either the super user, or the user $theUser
	 * Used when modifying user preference: this usually requires super user or being the user itself.
	 * 
	 * @param string $theUser
	 * @return bool
	 */
	static public function isUserIsSuperUserOrTheUser( $theUser )
	{
		try{
			self::checkUserIsSuperUserOrTheUser( $theUser );
			return true;
		} catch( Exception $e){
			return false;
		}
	}
	
	/**
	 * @param string $theUser
	 * @throws exception if the user is neither the super user nor the user $theUser
	 */
	static public function checkUserIsSuperUserOrTheUser( $theUser )
	{
		try{
			if( Piwik::getCurrentUserLogin() !== $theUser)
			{
				// or to the super user
				Piwik::checkUserIsSuperUser();
			}
		} catch( Piwik_Access_NoAccessException $e){
			throw new Piwik_Access_NoAccessException("The user has to be either the Super User or the user '$theUser' itself.");
		}
	}
	
	/**
	 * Returns true if the current user is the Super User
	 * @return bool
	 */
	static public function isUserIsSuperUser()
	{
		try{
			self::checkUserIsSuperUser();
			return true;
		} catch( Exception $e){
			return false;
		}
	}
	
	/**
	 * Helper method user to set the current as Super User.
	 * This should be used with great care as this gives the user all permissions.
	 */
	static public function setUserIsSuperUser()
	{
		Zend_Registry::get('access')->setSuperUser();
	}
	
	static public function checkUserIsSuperUser()
	{
		Zend_Registry::get('access')->checkUserIsSuperUser();
	}
	
	static public function isUserHasAdminAccess( $idSites )
	{
		try{
			self::checkUserHasAdminAccess( $idSites );
			return true;
		} catch( Exception $e){
			return false;
		}
	}
	
	static public function checkUserHasAdminAccess( $idSites )
	{
		Zend_Registry::get('access')->checkUserHasAdminAccess( $idSites );
	}
	
	static public function isUserHasSomeAdminAccess()
	{
		try{
			self::checkUserHasSomeAdminAccess();
			return true;
		} catch( Exception $e){
			return false;
		}
	}
	
	static public function checkUserHasSomeAdminAccess()
	{
		Zend_Registry::get('access')->checkUserHasSomeAdminAccess();
	}
	
	static public function checkUserHasSomeViewAccess()
	{
		Zend_Registry::get('access')->checkUserHasSomeViewAccess();
	}
	
	static public function isUserHasViewAccess( $idSites )
	{
		try{
			self::checkUserHasViewAccess( $idSites );
			return true;
		} catch( Exception $e){
			return false;
		}
	}
	
	static public function checkUserHasViewAccess( $idSites )
	{
		Zend_Registry::get('access')->checkUserHasViewAccess( $idSites );
	}
	
	static public function prefixClass( $class )
	{
		if(substr_count($class, Piwik::CLASSES_PREFIX) > 0)
		{
			return $class;
		}
		return Piwik::CLASSES_PREFIX.$class;
	}
	static public function unprefixClass( $class )
	{
		$lenPrefix = strlen(Piwik::CLASSES_PREFIX);
		if(substr($class, 0, $lenPrefix) == Piwik::CLASSES_PREFIX)
		{
			return substr($class, $lenPrefix);
		}
		return $class;
	}

	/**
	 * Returns the current module read from the URL (eg. 'API', 'UserSettings', etc.)
	 *
	 * @return string
	 */
	static public function getModule()
	{
		return Piwik_Common::getRequestVar('module', '', 'string');
	}
	
	/**
	 * Returns the current action read from the URL
	 *
	 * @return string
	 */
	static public function getAction()
	{
		return Piwik_Common::getRequestVar('action', '', 'string');
	}
	
	/**
	 * returns false if the URL to redirect to is already this URL
	 */
	static public function redirectToModule( $newModule, $newAction = '' )
	{
		$currentModule = self::getModule();
		$currentAction = self::getAction();
	
		if($currentModule != $newModule
			||  $currentAction != $newAction )
		{
			
			$newUrl = 'index.php' . Piwik_Url::getCurrentQueryStringWithParametersModified(
						array('module' => $newModule, 'action' => $newAction)
				);
	
			Piwik_Url::redirectToUrl($newUrl);
		}
		return false;
	}

	/**
	 * Get "best" available transport method for sendHttpRequest() calls.
	 */
	static public function getTransportMethod()
	{
		$method = 'curl';
		if(!extension_loaded('curl'))
		{
			$method = 'stream';
			if(@ini_get('allow_url_fopen') != '1')
			{
				$method = 'socket';
				if(preg_match('/(^|,|\s)fsockopen($|,|\s)/', @ini_get('disable_functions')))
				{
					return null;
				}
			}
		}
		return $method;
	}

	/**
	 * Sends http request ensuring the request will fail before $timeout seconds
	 *
	 * If no $destinationPath is specified, the trimmed response (without header) is returned as a string.
	 * If a $destinationPath is specified, the response (without header) is saved to a file.
	 *
	 * @param string $aUrl
	 * @param int $timeout
	 * @param string $userAgent
	 * @param string $destinationPath
	 * @param int $followDepth
	 * @return true (or string) on success; false on HTTP response error code (1xx or 4xx); throws exception on all other errors
	 */
	static public function sendHttpRequest($aUrl, $timeout, $userAgent = null, $destinationPath = null, $followDepth = 0)
	{
		// create output file
		$file = null;
		if($destinationPath)
		{
			if (($file = @fopen($destinationPath, 'wb')) === false || !is_resource($file))
			{
				throw new Exception('Error while creating the file: ' . $destinationPath);
			}
		}

		return self::sendHttpRequestBy(self::getTransportMethod(), $aUrl, $timeout, $userAgent, $file, $followDepth); 			
	}

	static public function sendHttpRequestBy($method = 'socket', $aUrl, $timeout, $userAgent = null, $file = null, $followDepth = 0)
	{
		if ($followDepth > 3)
		{
			throw new Exception('Too many redirects ('.$followDepth.')');
		}

		$contentLength = 0;

		if($method == 'socket')
		{
			// initialization
			$url = @parse_url($aUrl);
			if($url === false || !isset($url['scheme']))
			{
				throw new Exception('Malformed URL: '.$aUrl);
			}

			if($url['scheme'] != 'http')
			{
				throw new Exception('Invalid protocol/scheme: '.$url['scheme']);
			}
			$host = $url['host'];
			$port = isset($url['port)']) ? $url['port'] : 80;
			$path = isset($url['path']) ? $url['path'] : '/';
			if(isset($url['query']))
			{
				$path .= '?'.$url['query'];
			}
			$errno = null;
			$errstr = null;

			// connection attempt
			if (($fsock = @fsockopen($host, $port, $errno, $errstr, $timeout)) === false || !is_resource($fsock))
			{
				if(is_resource($file)) { @fclose($file); }
				throw new Exception("Error while connecting to: $host. Please try again later. $errstr");
			}

			// send HTTP request header
			fwrite($fsock,
				"GET $path HTTP/1.0\r\n"
				."Host: $host".($port != 80 ? ':'.$port : '')."\r\n"
				."User-Agent: Piwik/".Piwik_Version::VERSION.($userAgent ? " $userAgent" : '')."\r\n"
				.'Referer: http://'.Piwik_Common::getIpString()."/\r\n"
				."Connection: close\r\n"
				."\r\n"
			);

			$streamMetaData = array('timed_out' => false);
			@stream_set_blocking($fsock, true);
			@stream_set_timeout($fsock, $timeout);

			// process header
			$status = null;
			$expectRedirect = false;
			$fileLength = 0;

			while (!feof($fsock))
			{
				$line = fgets($fsock, 4096);

				$streamMetaData = @stream_get_meta_data($fsock);
				if($streamMetaData['timed_out'])
				{
					if(is_resource($file)) { @fclose($file); }
					@fclose($fsock);
					throw new Exception('Timed out waiting for server response');
				}

				// a blank line marks the end of the server response header
				if(rtrim($line, "\r\n") == '')
				{
					break;
				}

				// parse first line of server response header
				if(!$status)
				{
					// expect first line to be HTTP response status line, e.g., HTTP/1.1 200 OK
					if(!preg_match('~^HTTP/(\d\.\d)\s+(\d+)(\s*.*)?~', $line, $m))
					{
						if(is_resource($file)) { @fclose($file); }
						@fclose($fsock);
						throw new Exception('Expected server response code.  Got '.rtrim($line, "\r\n"));
					}

					$status = (integer) $m[2];

					// Informational 1xx or Client Error 4xx
					if ($status < 200 || $status >= 400)
					{
						if(is_resource($file)) { @fclose($file); }
						@fclose($s);
						return false;
					}

					continue;
				}

				// handle redirect
				if(preg_match('/^Location:\s*(.+)/', rtrim($line, "\r\n"), $m))
				{
					if(is_resource($file)) { @fclose($file); }
					@fclose($s);
					// Successful 2xx vs Redirect 3xx
					if($status < 300)
					{
						throw new Exception('Unexpected redirect to Location: '.rtrim($line).' for status code '.$status);
					}
					return self::sendHttpRequest(trim($m[1]), $pathDestination, $tries+1);
				}

				// save expected content length for later verification
				if(preg_match('/^Content-Length:\s*(\d+)/', $line, $m))
				{
					$contentLength = (integer) $m[1];
				}
			}

			if(feof($fsock))
			{
				throw new Exception('Unexpected end of transmission');
			}

			// process content/body
			$response = '';

			while (!feof($fsock))
			{
				$line = fread($fsock, 8192);

				$streamMetaData = @stream_get_meta_data($fsock);
				if($streamMetaData['timed_out'])
				{
					if(is_resource($file)) { @fclose($file); }
					@fclose($fsock);
					throw new Exception('Timed out waiting for server response');
				}

				if(is_resource($file))
				{
					// save to file
					$fileLength += fwrite($file, $line);
				}
				else
				{
					// concatenate to response string
					$response .= $line;
				}
			}

			// determine success or failure
			@fclose(@$fsock);
		}
		else if($method == 'stream')
		{
			$response = false;

			// we make sure the request takes less than a few seconds to fail
			// we create a stream_context (works in php >= 5.2.1)
			// we also set the socket_timeout (for php < 5.2.1)
			$default_socket_timeout = @ini_get('default_socket_timeout');
			@ini_set('default_socket_timeout', $timeout);

			$ctx = null;
			if(function_exists('stream_context_create')) {
				$stream_options = array(
					'http' => array(
						'header' => 'User-Agent: Piwik/'.Piwik_Version::VERSION.($userAgent ? " $userAgent" : '')."\r\n"
						           .'Referer: http://'.Piwik_Common::getIpString()."/\r\n",
						'max_redirects' => 3, // PHP 5.1.0
						'timeout' => $timeout, // PHP 5.2.1
					)
				);
				$ctx = stream_context_create($stream_options);
			}

			$response = @file_get_contents($aUrl, 0, $ctx);
			if(is_resource($file))
			{
				// save to file
				fwrite($file, $response);
			}

			// restore the socket_timeout value
			if(!empty($default_socket_timeout))
			{
				@ini_set('default_socket_timeout', $default_socket_timeout);
			}
		}
		else if($method == 'curl')
		{
			$ch = @curl_init();

			$curl_options = array(
				CURLOPT_URL => $aUrl,
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_BINARYTRANSFER => is_resource($file),
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 3,
				CURLOPT_USERAGENT => 'Piwik/'.Piwik_Version::VERSION.($userAgent ? " $userAgent" : ''),
				CURLOPT_REFERER => 'http://'.Piwik_Common::getIpString(),
			);
			@curl_setopt_array($ch, $curl_options);

			$response = @curl_exec($ch);
			if(is_resource($file))
			{
				// save to file
				fwrite($file, $response);
			}

			@curl_close($ch);
			unset($ch);
		}
		else
		{
			throw new Exception('Invalid request method: '.$method);
		}

		if(is_resource($file))
		{
			fflush($file);
			@fclose($file);
			if($contentLength && ($fileLength != $contentLength))
			{
				throw new Exception('File size error: '.$destinationPath.'; expected '.$contentLength.' bytes; received '.$fileLength.' bytes');
			}
			return true;
		}

		if($contentLength && strlen($response) != $contentLength)
		{
			throw new Exception('Content length error: expected '.$contentLength.' bytes; received '.$fileLength.' bytes');
		}
		return trim($response);
	}

	/**
	 * Fetch the file at $url in the destination $pathDestination
	 * @param string $url
	 * @param string $pathDestination
	 * @param int $tries
	 * @return true on success, throws Exception on failure
	 */
	static public function fetchRemoteFile($url, $pathDestination, $tries = 0)
	{
		return self::sendHttpRequest($url, 10, 'Update', $pathDestination, $tries);
	}

	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir Directory name
	 * @param boolean $deleteRootToo Delete specified top-level directory as well
	 */
	static public function unlinkRecursive($dir, $deleteRootToo)
	{
		if(!$dh = @opendir($dir))
		{
			return;
		}
		while (false !== ($obj = readdir($dh)))
		{
			if($obj == '.' || $obj == '..')
			{
				continue;
			}
	
			if (!@unlink($dir . '/' . $obj))
			{
				self::unlinkRecursive($dir.'/'.$obj, true);
			}
		}
		closedir($dh);
		if ($deleteRootToo)
		{
			@rmdir($dir);
		}
		return;
	} 
	
	/**
	 * Copy recursively from $source to $target.
	 * 
	 * @param string $source eg. './tmp/latest'
	 * @param string $target eg. '.'
	 * @param bool   $excludePhp
	 */
	static public function copyRecursive($source, $target, $excludePhp=false )
	{
		if ( is_dir( $source ) )
		{
			@mkdir( $target );
			$d = dir( $source );
			while ( false !== ( $entry = $d->read() ) )
			{
				if ( $entry == '.' || $entry == '..' )
				{
					continue;
				}
			   
				$sourcePath = $source . '/' . $entry;		   
				if ( is_dir( $sourcePath ) )
				{
					self::copyRecursive( $sourcePath, $target . '/' . $entry, $excludePhp );
					continue;
				}
				$destPath = $target . '/' . $entry;
				self::copy($sourcePath, $destPath, $excludePhp);
			}
			$d->close();
		}
		else
		{
			self::copy($source, $target, $excludePhp);
		}
	}
	
	/**
	 * Copy individual file from $source to $target.
	 * 
	 * @param string $source eg. './tmp/latest/index.php'
	 * @param string $target eg. './index.php'
	 * @param bool   $excludePhp
	 * @return bool
	 */
	static public function copy($source, $dest, $excludePhp=false)
	{
		static $phpExtensions = array('php', 'tpl');

		if($excludePhp)
		{
			$path_parts = pathinfo($source);
			if(in_array($path_parts['extension'], $phpExtensions))
			{
				return true;
			}
		}

		if(!@copy( $source, $dest ))
		{
			@chmod($dest, 0755);
	   		if(!@copy( $source, $dest )) 
	   		{
				throw new Exception("
				Error while copying file to <code>$dest</code>. <br />
				Please check that the web server has enough permission to overwrite this file. <br/>
				For example, on a linux server, if your apache user is www-data you can try to execute:<br>
				<code>chown -R www-data:www-data ".Piwik_Common::getPathToPiwikRoot()."</code><br>
				<code>chmod -R 0755 ".Piwik_Common::getPathToPiwikRoot()."</code><br>
					");
	   		}
		}
		return true;
	}

	/**
	 * Recursively find pathnames that match a pattern
	 * @see glob()
	 *
	 * @param string $sDir directory
	 * @param string $sPattern pattern
	 * @param int $nFlags glob() flags
	 * @return array
	 */
	public static function globr($sDir, $sPattern, $nFlags = NULL)
	{
		$aFiles = glob("$sDir/$sPattern", $nFlags);
		if(($aDirs = glob("$sDir/*", GLOB_ONLYDIR)) != false)
		{
			foreach ($aDirs as $sSubDir)
			{
				$aSubFiles = self::globr($sSubDir, $sPattern, $nFlags);
				$aFiles = array_merge($aFiles, $aSubFiles);
			}
		}
		return $aFiles;
	}

	/**
	 * API was simplified in 0.2.27, but we maintain backward compatibility 
	 * when calling Piwik::prefixTable
	 *
	 * @deprecated as of 0.2.27
	 */
	static public function prefixTable( $table )
	{
		return Piwik_Common::prefixTable($table);
	}
	
	/**
	 * Names of all the prefixed tables in piwik
	 * Doesn't use the DB 
	 */
	static public function getTablesNames()
	{
		$aTables = array_keys(self::getTablesCreateSql());
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$return = array();
		foreach($aTables as $table)
		{
			$return[] = $prefixTables.$table;
		}
		return $return;
	}
	
	static $tablesInstalled = null;
	
	static public function getTablesInstalled($forceReload = true,  $idSite = null)
	{
		if(is_null(self::$tablesInstalled)
			|| $forceReload === true)
		{
			$db = Zend_Registry::get('db');
			$config = Zend_Registry::get('config');
			$prefixTables = $config->database->tables_prefix;
			
			$allTables = $db->fetchCol("SHOW TABLES");
			
			// all the tables to be installed
			$allMyTables = self::getTablesNames();
			
			// we get the intersection between all the tables in the DB and the tables to be installed
			$tablesInstalled = array_intersect($allMyTables, $allTables);
			
			// at this point we have only the piwik tables which is good
			// but we still miss the piwik generated tables (using the class Piwik_TablePartitioning)
			$idSiteInSql = "no";
			if(!is_null($idSite))
			{
				$idSiteInSql = $idSite;
			}
			$allArchiveNumeric = $db->fetchCol("/* SHARDING_ID_SITE = ".$idSiteInSql." */ 
												SHOW TABLES LIKE '".$prefixTables."archive_numeric%'");
			$allArchiveBlob = $db->fetchCol("/* SHARDING_ID_SITE = ".$idSiteInSql." */ 
												SHOW TABLES LIKE '".$prefixTables."archive_blob%'");
					
			$allTablesReallyInstalled = array_merge($tablesInstalled, $allArchiveNumeric, $allArchiveBlob);
			
			self::$tablesInstalled = $allTablesReallyInstalled;
		}
		return 	self::$tablesInstalled;
	}
	
	static public function createDatabase( $dbName = null )
	{
		if(is_null($dbName))
		{
			$dbName = Zend_Registry::get('config')->database->dbname;
		}
		Piwik_Exec("CREATE DATABASE IF NOT EXISTS ".$dbName);
	}

	static public function dropDatabase()
	{
		$dbName = Zend_Registry::get('config')->database->dbname;
		Piwik_Exec("DROP DATABASE IF EXISTS " . $dbName);
	}
	
	static public function createDatabaseObject( $dbInfos = null )
	{
		$config = Zend_Registry::get('config');
		
		if(is_null($dbInfos))
		{
			$dbInfos = $config->database->toArray();
		}
		
		$dbInfos['profiler'] = $config->Debug->enable_sql_profiler;
		
		$db = null;
		Piwik_PostEvent('Reporting.createDatabase', $db);
		if(is_null($db))
		{
			if($dbInfos['port'][0] == '/')
			{
				$dbInfos['unix_socket'] = $dbInfos['port'];
				unset($dbInfos['host']);
				unset($dbInfos['port']);
			}

			// not used by Zend Framework
			unset($dbInfos['tables_prefix']);
			unset($dbInfos['adapter']);

			$db = Piwik_Db::factory($config->database->adapter, $dbInfos);
			$db->getConnection();

			Zend_Db_Table::setDefaultAdapter($db);
			$db->resetConfig(); // we don't want this information to appear in the logs
		}
		Zend_Registry::set('db', $db);
	}
	
	static public function disconnectDatabase()
	{
		Zend_Registry::get('db')->closeConnection();
	}
	
	/**
	 * Returns the MySQL database server version
	 *
	 * @deprecated 0.4.4
	 */
	static public function getMysqlVersion()
	{
		return Piwik_FetchOne("SELECT VERSION()");
	}

	/**
	 * Checks the database server version against the required minimum
	 * version.
	 *
	 * @see config/global.ini.php
	 * @since 0.4.4
	 * @throws Exception if server version is less than the required version
	 */
	static public function checkDatabaseVersion()
	{
		Zend_Registry::get('db')->checkServerVersion();
	}

	/**
	 * Check database connection character set is utf8.
	 *
	 * @return bool True if it is (or doesn't matter); false otherwise
	 */
	static public function isDatabaseConnectionUTF8()
	{
		return Zend_Registry::get('db')->isConnectionUTF8();
	}

	static public function createLogObject()
	{
		$configAPI = Zend_Registry::get('config')->log;
		
		$aLoggers = array(
				'logger_api_call' => new Piwik_Log_APICall,
				'logger_exception' => new Piwik_Log_Exception,
				'logger_error' => new Piwik_Log_Error,
				'logger_message' => new Piwik_Log_Message,
			);			
			
		foreach($configAPI as $loggerType => $aRecordTo)
		{
			if(isset($aLoggers[$loggerType]))
			{
				$logger = $aLoggers[$loggerType];
				
				foreach($aRecordTo as $recordTo)
				{
					switch($recordTo)
					{
						case 'screen':
							$logger->addWriteToScreen();
						break;
						
						case 'database':
							$logger->addWriteToDatabase();
						break;
						
						case 'file':
							$logger->addWriteToFile();		
						break;
						
						default:
							throw new Exception("'$recordTo' is not a valid Log type. Valid logger types are: screen, database, file.");
						break;
					}
				}
			}
		}
		
		foreach($aLoggers as $loggerType =>$logger)
		{
			if($logger->getWritersCount() == 0)
			{
				$logger->addWriteToNull();
			}
			Zend_Registry::set($loggerType, $logger);
		}
	}
	
	static public function createConfigObject( $pathConfigFile = null )
	{
		$config = new Piwik_Config($pathConfigFile);
		Zend_Registry::set('config', $config);
		$config->init();
	}

	static public function createAccessObject()
	{
		Zend_Registry::set('access', new Piwik_Access());
	}
	
	static public function dropTables( $doNotDelete = array() )
	{
		$tablesAlreadyInstalled = self::getTablesInstalled();
		$db = Zend_Registry::get('db');
		
		$doNotDeletePattern = '/('.implode('|',$doNotDelete).')/';
		
		foreach($tablesAlreadyInstalled as $tableName)
		{
			
			if( count($doNotDelete) == 0
				|| (!in_array($tableName,$doNotDelete)
					&& !preg_match($doNotDeletePattern,$tableName)
					)
				)
			{
				$db->query("DROP TABLE `$tableName`");
			}
		}			
	}
	
	/**
	 * Returns true if the email is a valid email
	 * 
	 * @param string email
	 * @return bool
	 */
	static public function isValidEmailString( $email ) 
	{
		return (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,4}$/', $email) > 0);
	}
	
	/**
	 * Creates an entry in the User table for the "anonymous" user. 
	 */
	static public function createAnonymousUser()
	{
		// The anonymous user is the user that is assigned by default 
		// note that the token_auth value is anonymous, which is assigned by default as well in the Login plugin
		$db = Zend_Registry::get('db');
		$db->query("INSERT INTO ". Piwik::prefixTable("user") . " 
					VALUES ( 'anonymous', '', 'anonymous', 'anonymous@example.org', 'anonymous', CURRENT_TIMESTAMP );" );
	}
	
	static public function createTables()
	{
		$db = Zend_Registry::get('db');
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;

		$tablesAlreadyInstalled = self::getTablesInstalled();
		$tablesToCreate = self::getTablesCreateSql();
		unset($tablesToCreate['archive_blob']);
		unset($tablesToCreate['archive_numeric']);

		foreach($tablesToCreate as $tableName => $tableSql)
		{
			$tableName = $prefixTables . $tableName;
			if(!in_array($tableName, $tablesAlreadyInstalled))
			{
				$db->query( $tableSql );
			}
		}
	}
	
	static public function truncateAllTables()
	{
		$tablesAlreadyInstalled = self::getTablesInstalled($forceReload = true);
		foreach($tablesAlreadyInstalled as $table) 
		{
			Piwik_Query("TRUNCATE `$table`");
		}
	}
	
	static public function install()
	{
		Piwik_Common::mkdir(Zend_Registry::get('config')->smarty->compile_dir);
	}
	
	static public function uninstall()
	{
		$db = Zend_Registry::get('db');
		$db->query( "DROP TABLE IF EXISTS ". implode(", ", self::getTablesNames()) );
	}
}

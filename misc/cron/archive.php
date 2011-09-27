<?php
$USAGE = "
Usage: /path/to/cli/php ".@$_SERVER['argv'][0]." <hostname> [<current_periods_timeout> [<reset|forceall>]]
<hostname>: Piwik hostname eg. localhost, localhost/piwik
<current_periods_timeout>: Current week/month/year will be processed at most every <current_periods_timeout> seconds. Defaults to 3600.
<reset[window_back_seconds]|forceall>: you can either specify 
	- reset: the script will run as if it was never executed before, therefore will trigger archiving on all websites with some traffic in the last 7 days.
			You can specify a number of seconds to use instead of 7 days window, for example call archive.php 1 reset 86400 to archive all reports for all sites that had visits in the last 24 hours
	- forceall: the script will trigger archiving on all websites for all periods, sequentially
	- reset+forceall: you can also specify both, which is effectively the same behavior 
	as the slower script archive.sh. The only added optimization: it does not trigger archiving for periods 
	if the last 52 days have no data at all. 
	
This script should be executed every hour, or as a deamon.

For more help and documentation, try $ /path/to/cli/php ".@$_SERVER['argv'][0]." help
";


$HELP = "
= Description =
This script will automatically process all reports for websites tracked in Piwik. 
See for more information http://piwik.org/docs/setup-auto-archiving/
 
= Example usage =
$ /usr/bin/php /path/to/piwik/misc/cron/archive.php localhost/piwik 6200
This call will archive all websites reports calling the API on http://localhost/piwik/index.php?...
It will only process the current week / current month / current year more if the existing reports are older than 2 hours (6200s).
Setting a large timeout for periods ensures best performance when Piwik tracks thousands of websites or a few very high traffic sites.

$ /usr/bin/php /path/to/piwik/misc/cron/archive.php localhost/piwik 1
Setting <current_periods_timeout> to 1 ensures that whenever today's reports are processed, the current week/month/year will 
also be reprocessed. This is less efficient than setting a timeout, but ensures that all reports are kept up to date as often as possible.

= Sample output =
See this link for a sample output:  

= Requirements =
 * Requires PHP CLI and Curl php extension
 * It is recommended to disable browser based archiving as per documentation in: http://piwik.org/docs/setup-auto-archiving/

= More information =
This script is an optimized rewrite of archive.sh in PHP, allowing for more flexibility 
and better near real-time performance when Piwik tracks thousands of websites.

When executed, this script does the following:
- Fetches Super User token_auth from config file
- Calls API to get the list of all websites Ids with new visits since the last archive.php succesful run
- Calls API to get the list of segments to pre-process
The script then loops over these websites & segments and calls the API to pre-process these reports.
At the end, some basic metrics and processing time are logged on screen.

Notes about the algorithm:
- The first time it runs, all websites with traffic in the last 7 days will be processed
- To improve performance, API is called with date=last2 (to query yesterday and today) whenever possible, instead of last52.
  To do so, the script logs the last time it executed correctly.
- The script tries to achieve Near real time for \"today\" reports, processing \"period=day\" as frequently as possible.
- The script will only process (or re-process) reports for Current week / Current month  
	 or Current year at most once per hour. To do so, the script logs last execution time for each website.
  You can change this <current_periods_timeout> timeout as a parameter when calling archive.php script.
  The concept is to archive daily report as often as possible, to stay near real time on \"daily\" reports,
  while allowing more stale data for the current week/month/year reports. 
  
= Ideas for improvements =
- Once an hour max, and on request: run archiving for previousN for websites which days have just 
  finished in the last 2 hours in their timezones, then TODO uncomment when implemented full archiving
- Bug: when adding new segments to preprocess, script will assume that data was processed for this segment in the past
- FAQ + doc update, for using this archive.php instead of archive.sh/.ps1 to deprecate 

- FAQ for daemon like process. Run 2 separate for days and week/month/year? 
- 'reset' not compatible with concurrent threads
- scheduled task send multiple reports when concurrent threads
- prepare script to start multiple processes
- Run websites archiving in parallel, currently only segments are ran in parallel
- Queue Period archiving to be executed after today's reports with lower priority 
- Core: check that on first day of month, if request last month from UI, 
  it returns last temporary monthly report generated, if the last month haven't yet been processed / finalized
- Optimization: Run first most often requested websites, weighted by visits in the site (and/or time to generate the report)
  to run more often websites that are faster to process while processing often for power users using frequently piwik.
- UI: Add 'report last processed X s ago' in UI grey box 'About'
";
define('PIWIK_INCLUDE_PATH', realpath( dirname(__FILE__)."/../.." ));
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";

try {
	$archiving = new Archiving;
	$archiving->init();
	$archiving->run();
	$archiving->end();
} catch(Exception $e) {
	$archiving->logFatalError($e->getMessage());
}


class Archiving
{
	protected $piwikUrl = false;
	protected $token_auth = false;
	protected $processPeriodsMaximumEverySeconds = 3600;
	const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";
	const TRUNCATE_ERROR_MESSAGE_SUMMARY = 400;
	
	// Seconds window to look back to define "active websites" to archive on the first archive.php script execution 
	protected $firstRunActiveWebsitesWithTraffic = 604800; // 7 days
	
	protected $visits = 0;
	protected $requests = 0;
	
	protected $shouldResetState = false;
	protected $shouldArchiveAllWebsites = false;
	
	/**
	 * By default, will process last 52 days/weeks/months/year.
	 * It will be overwritten by the number of days since last archiving ran until completion.
	 */
	const DEFAULT_DATE_LAST = 52;
	protected $timeLastCompleted = false;
	
	protected $requestPrepend = '&trigger=archivephp';
	protected $errors = array();
	
	private function log($m)
	{
//		echo $m . "\n";
		Piwik::log($m);
	}
	
	/**
	 * Issues a request to $url
	 * TODO: Add retry
	 */
	protected function request($url)
	{
		$url = $this->piwikUrl. $url . $this->requestPrepend;
//		$this->log($url);

		try {
			$response = Piwik_Http::sendHttpRequestBy('curl', $url, $timeout = 300);
		} catch(Exception $e) {
			return $this->logNetworkError($url, $e->getMessage());
		}
		if($this->checkResponse($response, $url))
		{
			return $response;
		}
		return false;
	}
	
	private function logNetworkError($url, $response)
	{
		$this->logError("Got invalid response from API request: $url. Response was '$response'");
		return false;
	}
	
	private function checkResponse($response, $url)
	{
		if(empty($response)
			|| stripos($response, 'error')) {
			return $this->logNetworkError($url, $response);
		}
		return true;
	}
	
	private function logError($m)
	{
		$this->errors[] = substr($m, 0, self::TRUNCATE_ERROR_MESSAGE_SUMMARY);
		$this->log("ERROR: $m");
	}
	
	public function logFatalError($m)
	{
//		debug_print_backtrace();
		$this->log("ERROR: $m");
		trigger_error($m, E_USER_ERROR);
		exit;
	}
	
	/**
	 * Displays script usage
	 */
	protected function usage()
	{
		global $USAGE;
		$this->logLines($USAGE);
	}
	
	/**
	 * Displays script help
	 */
	protected function help()
	{
		global $HELP;
		$this->logLines($HELP);
	}
	
	private function logLines($t)
	{
		foreach(explode(PHP_EOL, $t) as $line) {
			$this->log($line);
		}
	}
	
	public function init()
	{
		// Init Piwik, connect DB, create log & config objects, etc.
		try {
			Piwik_FrontController::getInstance()->init();
		} catch(Exception $e) {
			echo "ERROR: During Piwik init, Message: ".$e->getMessage();
			exit;
		}
		
		// Make sure this is executed in CLI only (no web access)
		if(!Piwik_Common::isPhpCliMode())
		{
			die("This script archive.php must only be executed only in command line CLI mode. <br/>
			In a shell, execute for example the following to trigger archiving on the local Piwik server available at 'localhost/piwik'<br/>
			<code>$ /path/to/php /path/to/piwik/misc/cron/archive.php localhost/piwik</code>");
		}
		
		// Setting up Logging configuration: log on screen all messages for the script run
		Zend_Registry::get('config')->disableSavingConfigurationFileUpdates();
		Zend_Registry::get('config')->log->log_only_when_debug_parameter = 0;
		Zend_Registry::get('config')->log->logger_message = array("logger_message" => "screen");
		Piwik::createLogObject();
		
		// Verify requirements
		if(!function_exists("curl_multi_init")) {
			$this->log("ERROR: this script requires curl extension php_curl enabled in your CLI php.ini");
			$this->usage();
			exit;
		}
		
		// Verify script is called with server URL
		if ($_SERVER['argc'] < 2
			|| $_SERVER['argc'] >5) {
			$this->usage();
		    exit;
		}
		
		// Display usage & help
		if ($_SERVER['argc'] == 2
			&& in_array($_SERVER['argv'][1], array("help", "-h", "h")))
		{
			$this->usage();
			$this->help();
			exit;
		}
		
		// Testing timeout parameter
		if ($_SERVER['argc'] == 3) {
			$_SERVER['argv'][2] = trim($_SERVER['argv'][2]);
			if(!is_numeric($_SERVER['argv'][2]))
			{
				$this->log("Expecting <current_periods_timeout> to be a number of seconds, got {$_SERVER['argv'][2]}");
				$this->usage();
				exit;
			}
			$this->processPeriodsMaximumEverySeconds = (int)$_SERVER['argv'][2];
			
			// Ensure the cache for periods is at least as high as cache for today
			$this->processPeriodsMaximumEverySeconds = max($this->processPeriodsMaximumEverySeconds, Piwik_ArchiveProcessing::getTodayArchiveTimeToLive());
			if($this->processPeriodsMaximumEverySeconds != $_SERVER['argv'][2])
			{
				$this->log("WARNING: Automatically increasing <current_periods_timeout> from {$_SERVER['argv'][2]} to {$this->processPeriodsMaximumEverySeconds} to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");
			}
		}
		// Recommend to disable browser archiving when using this script
		if( Piwik_ArchiveProcessing::isBrowserTriggerArchivingEnabled() )
		{
			//TODO uncomment when implemented full archiving
			//$this->log("NOTE: you should probably disable Browser archiving in Piwik UI > Settings > General Settings. See doc at: http://piwik.org/docs/setup-auto-archiving/");
		}
		if ($_SERVER['argc'] == 4
			|| $_SERVER['argc'] == 5) 
		{
			$isResetAndForceAll = $_SERVER['argv'][3] == "reset+forceall" || $_SERVER['argv'][3] == "forceall+reset";
			if($_SERVER['argv'][3] == "reset"
				|| $isResetAndForceAll)
			{
				$this->log("NOTE: 'reset' option was detected: the script will run as if it was never executed before");
				$this->shouldResetState = true;
				
				if(!$isResetAndForceAll
					&& is_numeric(@$_SERVER['argv'][4])
					&& $_SERVER['argv'][4] > 0)
				{
					$this->firstRunActiveWebsitesWithTraffic = (int)$_SERVER['argv'][4];
				}
			}
			if($_SERVER['argv'][3] == "forceall"
				|| $isResetAndForceAll)
			{
				$this->log("NOTE: 'forceall' option was detected: the script will archive all websites and all periods sequentially");
				$this->shouldArchiveAllWebsites = true;
			}
		}
		
		$this->timeLastCompleted = Piwik_GetOption(self::OPTION_ARCHIVING_FINISHED_TS);
		if($this->shouldResetState)
		{
			$this->timeLastCompleted = false;
		}
		
		// Fetching websites to process
		Piwik::setUserIsSuperUser(true);
		$this->allWebsites = Piwik_SitesManager_API::getInstance()->getAllSitesId();
		
		if($this->shouldArchiveAllWebsites)
		{
			$this->websites = $this->allWebsites;
		}
		else
		{
			// List of websites to archive first
			$timestampActiveTraffic = $this->timeLastCompleted; 
			if(empty($timestampActiveTraffic))
			{
				$timestampActiveTraffic = time() - $this->firstRunActiveWebsitesWithTraffic;
				$this->log("NOTE: in 'reset' mode, we will process all websites with visits in the last ". Piwik::getPrettyTimeFromSeconds($this->firstRunActiveWebsitesWithTraffic, true, false));
				
			}
			$this->websites = Piwik_SitesManager_API::getInstance()->getSitesIdWithVisits( $timestampActiveTraffic );
		}
		
		$this->logSection("INIT");
		$this->piwikUrl ="http://{$_SERVER['argv'][1]}/index.php";
		$this->log("Querying Piwik API at: {$this->piwikUrl}");		
		
		// Fetching super user token_auth
		$login = Zend_Registry::get('config')->superuser->login;
		$password = Zend_Registry::get('config')->superuser->password;
		$this->token_auth = $this->request("?module=API&method=UsersManager.getTokenAuth&userLogin=$login&md5Password=$password&format=php&serialize=0");
		if(strlen($this->token_auth) != 32 ) {
			$this->logFatalError("token_auth is expected to be 32 characters long. Got a different response '". substr($this->token_auth,0,100)."'");
		}
		$this->log("Running as Super User: $login");
		
		// Fetching segments to process
		$this->segments = Piwik_CoreAdminHome_API::getInstance()->getKnownSegmentsToArchive();
		if(empty($this->segments)) $this->segments = array();
		
		$this->log("Notes");
		// Information about timeout
		$this->todayArchiveTimeToLive = Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
		$this->log("- Reports for today will be processed at most every ".Piwik_ArchiveProcessing::getTodayArchiveTimeToLive()." seconds. You can change this value in Piwik UI > Settings > General Settings");
		$this->log("- Reports for the current week/month/year will be refreshed at most every ".$this->processPeriodsMaximumEverySeconds." seconds");
	
		// Try and not request older data we know is already archived
		if($this->timeLastCompleted !== false)
		{
			$dateLast = time() - $this->timeLastCompleted;
			$this->log("- Archiving was last executed without error ".Piwik::getPrettyTimeFromSeconds($dateLast, true, $isHtml = false)." ago");
		}
		
		if($this->shouldArchiveAllWebsites)
		{
			$this->log("Will process ". count($this->websites). " websites (forceall)");
		}
		else
		{
			$this->log("Will process ". count($this->websites). " websites ".
						"with new visits since " . 
						Piwik::getPrettyTimeFromSeconds(
								empty($this->timeLastCompleted)
								? $this->firstRunActiveWebsitesWithTraffic
								: $dateLast, 
								true, false) 
								. " " 
								. (!empty($this->websites) 
										? ", IDs: ".implode(", ", $this->websites) 
										: ""
			));
		}
		$this->log("Segments to pre-process for each website and each period: ". (!empty($this->segments) ? implode(", ", $this->segments) : "none"));
		
	}

	/**
	 * Returns URL to process reports for the $idsite on a given period with no segment
	 */
	protected function getVisitsRequestUrl($idsite, $period, $lastTimestampWebsiteProcessed = false)
	{
		if(empty($lastTimestampWebsiteProcessed))
		{
			$dateLast = self::DEFAULT_DATE_LAST;
		}
		else
		{
			//Note: could be more clever here, not period=month&date=last52...
			// Enforcing last2 at minimum to work around timing issues and ensure we make most archives available
			$dateLast = floor( (time() - $lastTimestampWebsiteProcessed) / 86400) + 2;
			if($dateLast > self::DEFAULT_DATE_LAST) {
				$dateLast = self::DEFAULT_DATE_LAST;
			}
		}
		return "?module=API&method=VisitsSummary.getVisits&idSite=$idsite&period=$period&date=last".$dateLast."&format=php&token_auth=".$this->token_auth;
	}
	
	protected function lastRunKey($idsite)
	{
		return "lastRunArchive_$idsite";
	}
	
	/**
	 * Main function, runs archiving on all websites with new activity
	 */
	public function run()
	{
		$websitesWithVisitsSinceLastRun = 
			$skippedPeriodsArchivesWebsite = 
			$skippedDayArchivesWebsites =
			$skipped =
			$processed = 
			$archivedPeriodsArchivesWebsite = 0;
		$timer = new Piwik_Timer;
		
		$this->logSection("START");
		$this->log("Starting Piwik reports archiving...");
		
		foreach ($this->websites as $idsite) 
		{
			$requestsBefore = $this->requests;
		    if ($idsite > 0) 
		    {
		        $timerWebsite = new $timer;
		        
		        $lastTimestampWebsiteProcessedPeriods = $lastTimestampWebsiteProcessedDay = false;
				if(!$this->shouldResetState)
				{
		            $lastTimestampWebsiteProcessedPeriods = Piwik_GetOption( $this->lastRunKey($idsite, "periods") );
		            $lastTimestampWebsiteProcessedDay = Piwik_GetOption( $this->lastRunKey($idsite, "day") );
				}
	            
        		// For period other than days, we only re-process the reports at most
        		// 1) every $processPeriodsMaximumEverySeconds
        		$secondsSinceLastExecution = time() - $lastTimestampWebsiteProcessedPeriods;
        		// if timeout is more than 10 min, we account for a 5 min processing time, and allow trigger 1 min earlier
        		if($this->processPeriodsMaximumEverySeconds > 10 * 60)
        		{
        			$secondsSinceLastExecution += 5 * 60;
        		}
	            $shouldArchivePeriods = $secondsSinceLastExecution > $this->processPeriodsMaximumEverySeconds;
	            if(empty($lastTimestampWebsiteProcessedPeriods))
	            {
	        		// 2) OR always if script never executed for this website before
	            	$shouldArchivePeriods = true;
	            }
	            
	            // Test if we should process this website at all
	            $elapsedSinceLastArchiving = time() - $lastTimestampWebsiteProcessedDay;
	            if(!$shouldArchivePeriods
	            	&& $elapsedSinceLastArchiving < $this->todayArchiveTimeToLive) 
	            {
	            	$this->log("Skipped website id $idsite, already processed today's report in recent run, ".Piwik::getPrettyTimeFromSeconds($elapsedSinceLastArchiving, true, $isHtml = false)." ago, ".$timerWebsite);
	            	$skippedDayArchivesWebsites++;
	            	$skipped++;
	            	continue;
	            }
	            
	            // Fake that the request is already done, so that other archive.php
	            // running do not grab the same website from the queue
	            Piwik_SetOption( $this->lastRunKey($idsite, "day"), time() );
	            
	            $url = $this->getVisitsRequestUrl($idsite, "day", 
						            // when 'forceall' option, also forces to archive last52 days to be safe
	            					$this->shouldArchiveAllWebsites ? false : $lastTimestampWebsiteProcessedDay);
	            $response = $this->request($url);
	            
	            if(empty($response))
	            {
	            	// cancel the succesful run flag
	            	Piwik_SetOption( $this->lastRunKey($idsite, "day"), 0 );
	            	
	            	$this->log("WARNING: Empty or invalid response for website id $idsite, ".$timerWebsite.", skipping");
	            	$skipped++;
	            	continue;
	            }
	            
	            $response = unserialize($response);
	            $visitsToday = end($response);
	            $this->requests++;
	            $processed++;
	            
		        // If there is no visit today and we don't need to process this website, we can skip remaining archives
	            if($visitsToday <= 0
	            	&& !$shouldArchivePeriods)
	            {
	            	$this->log("Skipped website id $idsite, no visit today, ".$timerWebsite);
	            	$skipped++;
	            	continue;
	            }
	            
	            $visitsAllDays = array_sum($response);
	            if($visitsAllDays == 0
	            	&& $shouldArchivePeriods
	            	&& $this->shouldArchiveAllWebsites
	            	)
	            {
	            	$this->log("Skipped website id $idsite, no visits in the last ".count($response)." days, ".$timerWebsite);
	            	$skipped++;
	            	continue;
	            }
	            $this->visits += $visitsToday;
	            $websitesWithVisitsSinceLastRun++;
	            $this->archiveVisitsAndSegments($idsite, "day", $lastTimestampWebsiteProcessedDay, $timerWebsite);
	            
	            // TODO: Queue
	        	if($shouldArchivePeriods)
        		{
        			$success = true;
			        foreach (array('week', 'month', 'year') as $period) 
			        {
	        			$success = $this->archiveVisitsAndSegments($idsite, $period, $lastTimestampWebsiteProcessedPeriods) && $success;
	        		}
        			// Record succesful run of this website's periods archiving 
        			if($success)
        			{
        				Piwik_SetOption( $this->lastRunKey($idsite, "periods"), time() );
        			}
        			$archivedPeriodsArchivesWebsite++;
		        }
		        else
		        {
		        	$skippedPeriodsArchivesWebsite++;
		        }
		        $requestsWebsite = $this->requests - $requestsBefore;
		        
		        $debug = $this->shouldArchiveAllWebsites ? ", last days = $visitsAllDays visits" : "";
		        Piwik::log("Archived website id = $idsite, today = $visitsToday visits".$debug.", $requestsWebsite API requests, ". $timerWebsite ." [" . ($websitesWithVisitsSinceLastRun+$skipped) . "/" . count($this->websites) . " done]" );
		    }
		}
		
		$this->log("Done archiving!");
		
		$this->logSection("SUMMARY");
		$this->log("Total daily visits archived: ". $this->visits);

		$totalWebsites = count($this->allWebsites);
		$skipped = $totalWebsites - $websitesWithVisitsSinceLastRun;
		$this->log("Archived today's reports for $websitesWithVisitsSinceLastRun websites");
		$this->log("Archived week/month/year for $archivedPeriodsArchivesWebsite websites. ");
		$this->log("Skipped $skipped websites: no new visit since the last script execution");
		$this->log("Skipped $skippedDayArchivesWebsites websites day archiving: existing daily reports are less than {$this->todayArchiveTimeToLive} seconds old");
		$this->log("Skipped $skippedPeriodsArchivesWebsite websites week/month/year archiving: existing periods reports are less than {$this->processPeriodsMaximumEverySeconds} seconds old");
		$this->log("Total API requests: $this->requests");
		
		//DONE: done/total, visits, wtoday, wperiods, reqs, time, errors[count]: first eg.
		$percent = count($this->websites) == 0
						? ""
						: " ".round($processed * 100 / count($this->websites),0) ."%";
		$otherInParallel = $skippedDayArchivesWebsites;
		$this->log("done: ".
					$processed ."/". count($this->websites) . "" . $percent. ", ".
					$this->visits." v, $websitesWithVisitsSinceLastRun wtoday, $archivedPeriodsArchivesWebsite wperiods, ".
					$this->requests." req, ".round($timer->getTimeMs())." ms, ".
					(empty($this->errors) 
						? "no error" 
						: (count($this->errors) . " errors. eg. '". reset($this->errors)."'" ))
					);
		$this->log($timer);
		$this->logSection("SCHEDULED TASKS");
		$this->log("Starting Scheduled tasks... ");
		
		$this->request("?module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=".$this->token_auth);
		
		$this->log("done");
		
	}
	
	/**
	 * @return bool True on success, false if some request failed
	 */
	private function archiveVisitsAndSegments($idsite, $period, $lastTimestampWebsiteProcessed, $timerWebsite = false)
	{
		$timer = new Piwik_Timer;
	    $aCurl = array();
		$mh = curl_multi_init();
		$url = $this->piwikUrl . $this->getVisitsRequestUrl($idsite, $period, $lastTimestampWebsiteProcessed) . $this->requestPrepend;
	    // already processed above for "day"
	    if($period != "day")
	    {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_multi_add_handle($mh, $ch);
			$aCurl[$url] = $ch;
			$this->requests++;
	    }
	    $urlNoSegment = $url;
	    foreach ($this->segments as $segment) {
	    	$segmentUrl = $url.'&segment='.urlencode($segment);
			$ch = curl_init($segmentUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_multi_add_handle($mh, $ch);
			$aCurl[$segmentUrl] = $ch;
			$this->requests++;
	    }
	    $running=null;
	    do {
			usleep(10000);
			curl_multi_exec($mh,$running);
	    } while ($running > 0);
	
	    $success = true;
	    $visitsAllDaysInPeriod = false;
        foreach($aCurl as $url => $ch){
        	$content = curl_multi_getcontent($ch);
        	$sucessResponse = $this->checkResponse($content, $url);
            $success = $sucessResponse && $success;
            if($url == $urlNoSegment
            	&& $sucessResponse)
            {
            	$content = unserialize($content);
            	$visitsAllDaysInPeriod = @array_sum($content);
            }
        }

	    foreach ($aCurl as $ch) {
	    	curl_multi_remove_handle($mh, $ch);
	    }
	    curl_multi_close($mh);
	    
		$this->log("Archived website id = $idsite, period = $period, "
					. ($period != "day" ? (int)$visitsAllDaysInPeriod. " visits, " : "" )
                    . (!empty($timerWebsite) ? $timerWebsite : $timer));
	    return $success;
	}
	
	
	/**
	 * Logs a section in the output
	 */
	private function logSection($title="")
	{
		$this->log("---------------------------");
		$this->log($title);
	}
	
	/**
	 * End of the script
	 */
	public function end()
	{
		// How to test the error handling code?
		// - Generate some hits since last archive.php run
		// - Start the script, in the middle, shutdown apache, then restore
		// Some errors should be logged and script should succesfully finish and then report the errors and trigger a PHP error
		if(!empty($this->errors))
		{
			$this->logSection("SUMMARY OF ERRORS");
			
			foreach($this->errors as $error) {
				$this->log("Error: ". $error);
			}
			$summary = count($this->errors) . " total errors during this script execution, please investigate and try and fix these errors";
			$this->log($summary);
			
			$summary .= '. First error was: '. reset($this->errors);
			$this->logFatalError($summary);
		}
		else
		{
			// No error -> Logs the succesful script execution until completion
			Piwik_SetOption(self::OPTION_ARCHIVING_FINISHED_TS, time());
		}
	}
}

<?php
$USAGE = "
Usage: 
	/path/to/cli/php \"".@$_SERVER['argv'][0]."\" --url=http://your-website.org/path/to/piwik/ [arguments]
	
Arguments:
	--url=[piwik-server-url]
			Mandatory argument. Must be set to the Piwik base URL. 
			For example: --url=http://analytics.example.org/ or --url=https://example.org/piwik/ 
	--force-all-websites
			If specified, the script will trigger archiving on all websites.
			This can be used along with --force-all-periods to trigger archiving on all websites and all periods.
	--force-all-periods[=seconds]
			Triggers archiving on websites with some traffic in the last [seconds] seconds.
			[seconds] defaults to 604800 which is 7 days.
			For example: --force-all-periods=86400 will archive websites that had visits in the last 24 hours.
	--force-timeout-for-periods=[seconds]
			The current week/ current month/ current year will be processed at most every [seconds].
			If not specified, defaults to 3600.
	--accept-invalid-ssl-certificate
			It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!
			It can be useful if you specified --url=https://... or if you are using Piwik with force_ssl=1
	--help
			Displays usage

Notes:
	* It is recommended to run the script with the argument --url=[piwik-server-url] only. Other arguments are not required. 
	* This script should be executed every hour via crontab, or as a deamon.
	* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'), 
	  but it is recommended to run it via command line/CLI instead.
	* If you use Piwik to track dozens/hundreds of websites, please let the team know at hello@piwik.org
	  it makes us happy to learn successful user stories :)
	* Enjoy!
";
/*
Ideas for improvements:
	- Feature request: Add option to log completion even with errors: archive_script_ignore_errors = 0
	- Known bug: when adding new segments to preprocess, script will assume that data was processed for this segment in the past
	- Document: how to run the script as a daemon for near real time / constant processing
	- The script can be executed multiple times in parrallel but with known issues:
	  - scheduled task could send multiple reports 
	  - there is no documentation
	  - it does not work well with --force-all-periods etc.
	- Possible performance improvement: Run first websites which are faster to process (weighted by visits and/or time to generate the last daily report)
	  This would make sure that huge websites do not 'block' processing of smaller websites' reports.  
	- Core: check that on first day of month, if request last month from UI, 
	  it returns last temporary monthly report generated, if the last month haven't yet been processed / finalized
 */
define('PIWIK_INCLUDE_PATH', realpath( dirname(__FILE__)."/../.." ));
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
define('PIWIK_MODE_ARCHIVE', true);
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
	const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";
	const TRUNCATE_ERROR_MESSAGE_SUMMARY = 400;
	
	// Seconds window to look back to define "active websites" to archive on the first archive.php script execution 
	protected $firstRunActiveWebsitesWithTraffic = 604800; // 7 days
	
	// By default, we only process the current week/month/year at most once an hour
	protected $processPeriodsMaximumEverySeconds = 3600;
	
	protected $websiteDayHasFinishedSinceLastRun = array();
	protected $idSitesInvalidatedOldReports = array();
	protected $piwikUrl = false;
	protected $token_auth = false;
	protected $visits = 0;
	protected $requests = 0;
	protected $output = '';
	protected $shouldResetState = false;
	protected $shouldArchiveAllWebsites = false;
	protected $acceptInvalidSSLCertificate = false;
	/**
	 * By default, will process last 52 days/weeks/months/year.
	 * It will be overwritten by the number of days since last archiving ran until completion.
	 */
	const DEFAULT_DATE_LAST = 52;
	
	protected $timeLastCompleted = false;
	protected $requestPrepend = '&trigger=archivephp';
	protected $errors = array();
	
	public function init()
	{
		$this->initCore();
		$this->initTokenAuth();
		$this->initCheckCli();
		$this->initLog();
		$this->displayHelp();
		$this->initPiwikHost();
		$this->initStateFromParameters();
		Piwik::setUserIsSuperUser(true);
		
		$this->logSection("INIT");
		$this->log("Querying Piwik API at: {$this->piwikUrl}");		
		$this->log("Running as Super User: " . $this->login);
		
		$this->acceptInvalidSSLCertificate = $this->isParameterSet("accept-invalid-ssl-certificate");
		
		// Test the specified piwik URL is valid
		$response = $this->request("?module=API&method=API.getDefaultMetricTranslations&format=php");
		$responseUnserialized = @unserialize($response);
		if($response === false
			|| !is_array($responseUnserialized))
		{
			$this->logFatalError("The Piwik URL {$this->piwikUrl} does not seem to be pointing to a Piwik server. Response was '$response'.");
		}
		
		$this->log("Notes");
		// Information about timeout
		$this->todayArchiveTimeToLive = Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
		$this->log("- Reports for today will be processed at most every ".Piwik_ArchiveProcessing::getTodayArchiveTimeToLive()
					." seconds. You can change this value in Piwik UI > Settings > General Settings.");
		$this->log("- Reports for the current week/month/year will be refreshed at most every "
					.$this->processPeriodsMaximumEverySeconds." seconds.");
		// Fetching segments to process
		$this->segments = Piwik_CoreAdminHome_API::getInstance()->getKnownSegmentsToArchive();
		if(empty($this->segments)) $this->segments = array();
		if(!empty($this->segments))
		{
			$this->log("- Segments to pre-process for each website and each period: ". implode(", ", $this->segments));
		}
	
		// Try and not request older data we know is already archived
		if($this->timeLastCompleted !== false)
		{
			$dateLast = time() - $this->timeLastCompleted;
			$this->log("- Archiving was last executed without error ".Piwik::getPrettyTimeFromSeconds($dateLast, true, $isHtml = false)." ago");
		}
		
		$this->initWebsitesToProcess();
		flush();
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
			// Enforcing last2 at minimum to work around timing issues and ensure we make most archives available
			$dateLast = floor( (time() - $lastTimestampWebsiteProcessed) / 86400) + 2;
			if($dateLast > self::DEFAULT_DATE_LAST) 
			{
				$dateLast = self::DEFAULT_DATE_LAST;
			}
		}
		return "?module=API&method=VisitsSummary.getVisits&idSite=$idsite&period=$period&date=last".$dateLast."&format=php&token_auth=".$this->token_auth;
	}
	
	protected function lastRunKey($idsite, $period)
	{
		return "lastRunArchive". $period ."_". $idsite;
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
		
		$this->websites = array_unique($this->websites);
		foreach ($this->websites as $idsite) 
		{
			flush();
			$requestsBefore = $this->requests;
		    if ($idsite <= 0) 
		    {
		    	continue;
		    }
		    
			$timerWebsite = new Piwik_Timer;
			
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
		    
		    // (*) If the website is archived because it is a new day in its timezone
		    // We make sure all periods are archived, even if there is 0 visit today
		    if(!$shouldArchivePeriods
		    	&& in_array($idsite, $this->websiteDayHasFinishedSinceLastRun))
		    {
		    	$shouldArchivePeriods = true;
		    }
		    
		    // (*) If there was some old reports invalidated for this website
		    // we make sure all these old reports are triggered at least once
		    $websiteIsOldDataInvalidate = in_array($idsite, $this->idSitesInvalidatedOldReports);
		    if($websiteIsOldDataInvalidate)
		    {
		    	$shouldArchivePeriods = true;
		    }
		    
		    // Test if we should process this website at all
		    
		    $elapsedSinceLastArchiving = time() - $lastTimestampWebsiteProcessedDay;
		    if(!$shouldArchivePeriods
		    	&& $elapsedSinceLastArchiving < $this->todayArchiveTimeToLive) 
		    {
		    	$this->log("Skipped website id $idsite, already processed today's report in recent run, "
					.Piwik::getPrettyTimeFromSeconds($elapsedSinceLastArchiving, true, $isHtml = false)
					." ago, ".$timerWebsite->__toString());
				$skippedDayArchivesWebsites++;
				$skipped++;
				continue;
		    }
		    
		    // Fake that the request is already done, so that other archive.php
		    // running do not grab the same website from the queue
		    Piwik_SetOption( $this->lastRunKey($idsite, "day"), time() );
		    
		    $url = $this->getVisitsRequestUrl($idsite, "day",
			    				// when some data was purged from this website
			    				// we make sure we query all previous days/weeks/months
		    				$websiteIsOldDataInvalidate
								// when --force-all-websites option, 
								// also forces to archive last52 days to be safe
							|| $this->shouldArchiveAllWebsites 
								? false 
								: $lastTimestampWebsiteProcessedDay
			);
		    $content = $this->request($url);
		    $response = @unserialize($content);
		    
		    if(empty($content)
		    	|| !is_array($response)
		    	|| count($response) == 0)
		    {
				// cancel the succesful run flag
				Piwik_SetOption( $this->lastRunKey($idsite, "day"), 0 );
				
				$this->log("WARNING: Empty or invalid response '$content' for website id $idsite, ".$timerWebsite->__toString().", skipping");
				$skipped++;
				continue;
		    }
            $visitsToday = end($response);
		    $this->requests++;
		    $processed++;
		    
			// If there is no visit today and we don't need to process this website, we can skip remaining archives
		    if($visitsToday <= 0
		    	&& !$shouldArchivePeriods)
		    {
				$this->log("Skipped website id $idsite, no visit today, ".$timerWebsite->__toString());
				$skipped++;
				continue;
		    }
		    
		    $visitsAllDays = array_sum($response);
		    if($visitsAllDays == 0
				&& !$shouldArchivePeriods
				&& $this->shouldArchiveAllWebsites
			)
		    {
				$this->log("Skipped website id $idsite, no visits in the last ".count($response)." days, ".$timerWebsite->__toString());
				$skipped++;
				continue;
		    }
		    $this->visits += $visitsToday;
		    $websitesWithVisitsSinceLastRun++;
		    $this->archiveVisitsAndSegments($idsite, "day", $lastTimestampWebsiteProcessedDay, $timerWebsite);
		    
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
					
					// Remove this website from the list of websites to be invalidated
					// since it now just been re-processing the reports, job is done!
					if( in_array($idsite, $this->idSitesInvalidatedOldReports ) )
					{
						$websiteIdsInvalidated = Piwik_CoreAdminHome_API::getWebsiteIdsToInvalidate();
						
						if(count($websiteIdsInvalidated))
						{
							$found = array_search($idsite, $websiteIdsInvalidated);
							if($found!==false)
							{
								unset($websiteIdsInvalidated[$found]);
//								$this->log("Websites left to invalidate: " . implode(", ", $websiteIdsInvalidated));
								Piwik_SetOption(Piwik_CoreAdminHome_API::OPTION_INVALIDATED_IDSITES, serialize($websiteIdsInvalidated));
							}
						}
					}
				}
				$archivedPeriodsArchivesWebsite++;
			}
			else
			{
				$skippedPeriodsArchivesWebsite++;
			}
			$requestsWebsite = $this->requests - $requestsBefore;
			
			$debug = $this->shouldArchiveAllWebsites ? ", last days = $visitsAllDays visits" : "";
			Piwik::log("Archived website id = $idsite, today = $visitsToday visits"
							.$debug.", $requestsWebsite API requests, "
							. $timerWebsite->__toString() 
							." [" . ($websitesWithVisitsSinceLastRun+$skipped) . "/" 
							. count($this->websites) 
							. " done]" );
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
		$this->log($timer->__toString());
		$this->logSection("SCHEDULED TASKS");
		$this->log("Starting Scheduled tasks... ");
		
		$tasksOutput = $this->request("?module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=".$this->token_auth);
		if($tasksOutput == "No data available")
		{
			$tasksOutput = " No task to run";
		}
		$this->log($tasksOutput);
		$this->log("done");
	}
	
	/**
	 * @return bool True on success, false if some request failed
	 */
	private function archiveVisitsAndSegments($idsite, $period, $lastTimestampWebsiteProcessed, Piwik_Timer $timerWebsite = null)
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
			
			if($this->acceptInvalidSSLCertificate)
			{
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false); 
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false); 
			}
			
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
            	$stats = unserialize($content);
            	if(!is_array($stats))
            	{
            		$this->logError("Error unserializing the following response: " . $content);
            	}
            	$visitsAllDaysInPeriod = @array_sum($stats);
            }
        }

	    foreach ($aCurl as $ch) {
	    	curl_multi_remove_handle($mh, $ch);
	    }
	    curl_multi_close($mh);
	    
		$this->log("Archived website id = $idsite, period = $period, "
					. ($period != "day" ? (int)$visitsAllDaysInPeriod. " visits, " : "" )
                    . (!empty($timerWebsite) ? $timerWebsite->__toString() : $timer->__toString()));
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
	

	private function log($m)
	{
	    $this->output .= $m . "\n";
		Piwik::log($m);
	}
	
	/**
	 * Issues a request to $url
	 */
	protected function request($url)
	{
		$url = $this->piwikUrl. $url . $this->requestPrepend;
		//$this->log($url);
		try {
			$response = Piwik_Http::sendHttpRequestBy('curl', $url, $timeout = 300, $userAgent = null, $destinationPath = null, $file = null, $followDepth = 0, $acceptLanguage = false, $acceptInvalidSSLCertificate = $this->acceptInvalidSSLCertificate);
		} catch(Exception $e) {
			return $this->logNetworkError($url, $e->getMessage());
		}
		if($this->checkResponse($response, $url))
		{
			return $response;
		}
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
	
	public function logFatalError($m, $backtrace = true)
	{
		$this->logError($m);
		$fe = fopen('php://stderr', 'w');
	    fwrite($fe, "Error in the last Piwik archive.php run: \n" . $m 
	            . ($backtrace ? "\n\n Here is the full errors output:\n\n" . $this->output : '') 
	    );
		trigger_error($m, E_USER_ERROR);
		exit;
	}
	
	private function logNetworkError($url, $response)
	{
		$message = "Got invalid response from API request: $url. ";
		if(empty($response))
		{
			$message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
		}
		else
		{
			$message .= "Response was '$response'";
		}
		$this->logError($message);
		return false;
	}
	
	/**
	 * Displays script usage
	 */
	protected function usage()
	{
		global $USAGE;
		$this->logLines($USAGE);
	}
	
	private function logLines($t)
	{
		foreach(explode(PHP_EOL, $t) as $line) 
		{
			$this->log($line);
		}
	}

	private function initLog()
	{
		$config = Piwik_Config::getInstance();
		$config->log['log_only_when_debug_parameter'] = 0;
		$config->log['logger_message'] = array("logger_message" => "screen");
		Piwik::createLogObject();
		
		if(!function_exists("curl_multi_init")) {
			$this->log("ERROR: this script requires curl extension php_curl enabled in your CLI php.ini");
			$this->usage();
			exit;
		}
	}
	
	/**
	 * Script does run on http:// ONLY if the SU token is specified
	 */
	private function initCheckCli()
	{
		if(!Piwik_Common::isPhpCliMode())
		{
			$token_auth = Piwik_Common::getRequestVar('token_auth', '', 'string');
			if($token_auth != $this->token_auth
				|| strlen($token_auth) != 32)
			{
				die('<b>You must specify the Super User token_auth as a parameter to this script, eg. <code>&token_auth=XYZ</code> if you wish to run this script through the browser. </b><br>
					However it is recommended to run it <a href="http://piwik.org/docs/setup-auto-archiving/">via cron in the command line</a>, since it can take a long time to run.<br/>
					In a shell, execute for example the following to trigger archiving on the local Piwik server:<br/>
					<code>$ /path/to/php /path/to/piwik/misc/cron/archive.php --url=http://your-website.org/path/to/piwik/</code>');
			}
		}
	}
	
	/**
	 * Init Piwik, connect DB, create log & config objects, etc.
	 */
	private function initCore()
	{
		try {
			Piwik_FrontController::getInstance()->init();
		} catch(Exception $e) {
			echo "ERROR: During Piwik init, Message: ".$e->getMessage();
			exit;
		}
	}
	
	private function displayHelp()
	{
		$displayHelp = $this->isParameterSet('help') || $this->isParameterSet('h');
		if ($displayHelp)
		{
			$this->usage();
			exit;
		}
	}

	protected function initStateFromParameters()
	{
		// Detect parameters 
		$reset = $this->isParameterSet("force-all-periods", $valuePossible = true);
		$forceAll = $this->isParameterSet("force-all-websites");
		$forceTimeoutPeriod = $this->isParameterSet("force-timeout-for-periods", $valuePossible = true);
		if(!empty($forceTimeoutPeriod)
			&& $forceTimeoutPeriod !== true) // in case --force-timeout-for-periods= without [seconds] specified
		{
			// Ensure the cache for periods is at least as high as cache for today
			$todayTTL = Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
			if($forceTimeoutPeriod < $todayTTL)
			{
				$this->log("WARNING: Automatically increasing --force-timeout-for-periods from $forceTimeoutPeriod to "
							. $todayTTL
							. " to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");
				$forceTimeoutPeriod = $todayTTL;
			}
			$this->processPeriodsMaximumEverySeconds = $forceTimeoutPeriod;
		}
		
		// Recommend to disable browser archiving when using this script
		if( Piwik_ArchiveProcessing::isBrowserTriggerArchivingEnabled() )
		{
			$this->log("NOTE: if you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Piwik UI > Settings > General Settings. ");
			$this->log("      see doc at: http://piwik.org/docs/setup-auto-archiving/");
		}
		
		if($reset)
		{
			$this->log("--force-all-periods was detected: the script will run as if it was its first run, and will trigger archiving for all periods.");
			$this->shouldResetState = true;
			
			if(!$forceAll
				&& is_numeric($reset)
				&& $reset > 0)
			{
				$this->firstRunActiveWebsitesWithTraffic = (int)$reset;
			}
		}
		
		if($forceAll)
		{
			$this->log("--force-all-websites was detected: the script will archive all websites and all periods sequentially");
			$this->shouldArchiveAllWebsites = true;
		}
		
		$this->timeLastCompleted = Piwik_GetOption(self::OPTION_ARCHIVING_FINISHED_TS);
		if($this->shouldResetState)
		{
			$this->timeLastCompleted = false;
		}
	}
	
	// Fetching websites to process
	protected function initWebsitesToProcess()
	{
		$this->allWebsites = Piwik_SitesManager_API::getInstance()->getAllSitesId();
		
		if($this->shouldArchiveAllWebsites)
		{
			$this->websites = $this->allWebsites;
			$this->log("Will process ". count($this->websites). " websites");
		}
		else
		{
			// 1) All websites with visits since the last archive.php execution
			$timestampActiveTraffic = $this->timeLastCompleted; 
			if(empty($timestampActiveTraffic))
			{
				$timestampActiveTraffic = time() - $this->firstRunActiveWebsitesWithTraffic;
				$this->log("--force-all-periods was detected: we will process websites with visits in the last "
						. Piwik::getPrettyTimeFromSeconds($this->firstRunActiveWebsitesWithTraffic, true, false)
				);
			}
			$this->websites = Piwik_SitesManager_API::getInstance()->getSitesIdWithVisits( $timestampActiveTraffic );
			$websiteIds = !empty($this->websites) ? ", IDs: ".implode(", ", $this->websites) : "";
			$prettySeconds = Piwik::getPrettyTimeFromSeconds(	empty($this->timeLastCompleted)
																	? $this->firstRunActiveWebsitesWithTraffic
																	: (time() - $this->timeLastCompleted), 
																true, false); 
			$this->log("Will process ". count($this->websites). " websites with new visits since " 
							. $prettySeconds 
							. " " 
							. $websiteIds);
							
			// 2) All websites that had reports in the past invalidated recently
			//	eg. when using Python log import script
			$this->idSitesInvalidatedOldReports = Piwik_CoreAdminHome_API::getWebsiteIdsToInvalidate();
			$this->idSitesInvalidatedOldReports = array_intersect($this->idSitesInvalidatedOldReports, $this->allWebsites);
			
			if(count($this->idSitesInvalidatedOldReports) > 0)
			{
				$websiteIds = ", IDs: ".implode(", ", $this->idSitesInvalidatedOldReports);
				$this->log("Will process ". count($this->idSitesInvalidatedOldReports). " other websites because some old data reports have been invalidated (eg. using the Log Import script) " . $websiteIds);
				$this->websites = array_merge($this->websites, $this->idSitesInvalidatedOldReports);
			}
			
			// 3) Also process all other websites which days have finished since the last run.
			//    This ensures we process the previous day/week/month/year that just finished, even if there was no new visit
			$uniqueTimezones = Piwik_SitesManager_API::getInstance()->getUniqueSiteTimezones();
			$timezoneToProcess = array();
			foreach($uniqueTimezones as &$timezone)
			{
				$processedDateInTz = Piwik_Date::factory((int)$timestampActiveTraffic, $timezone);
				$currentDateInTz = Piwik_Date::factory('now', $timezone);
				
				if($processedDateInTz->toString() != $currentDateInTz->toString() )
				{
					$timezoneToProcess[] = $timezone;
				}
			}
			
			$websiteDayHasFinishedSinceLastRun = Piwik_SitesManager_API::getInstance()->getSitesIdFromTimezones($timezoneToProcess);
			$websiteDayHasFinishedSinceLastRun = array_diff($websiteDayHasFinishedSinceLastRun, $this->websites);
			$this->websiteDayHasFinishedSinceLastRun = $websiteDayHasFinishedSinceLastRun;
			if(count($websiteDayHasFinishedSinceLastRun) > 0)
			{
				$websiteIds = !empty($websiteDayHasFinishedSinceLastRun) ? ", IDs: ".implode(", ", $websiteDayHasFinishedSinceLastRun) : "";
				$this->log("Will process ". count($websiteDayHasFinishedSinceLastRun). " other websites because the last time they were archived was on a different day (in the website's timezone) " . $websiteIds);
				
				$this->websites = array_merge($this->websites, $websiteDayHasFinishedSinceLastRun);
			}

			
		}
	}

	protected function initTokenAuth()
	{
		$login = Piwik_Config::getInstance()->superuser['login'];
		$md5Password = Piwik_Config::getInstance()->superuser['password'];
		$this->token_auth = md5( $login . $md5Password ) ;
		$this->login = $login;
	}
	
	private function initPiwikHost()
	{
		// If archive.php run as a web cron, we use the current hostname
		if(!Piwik_Common::isPhpCliMode())
		{
			// example.org/piwik/misc/cron/
			$piwikUrl = Piwik_Common::sanitizeInputValue(Piwik_Url::getCurrentUrlWithoutFileName());
			// example.org/piwik/
			$piwikUrl = $piwikUrl . "../../";
		}
		// If archive.php run as CLI/shell we require the piwik url to be set
		else
		{
			$piwikUrl = $this->isParameterSet("url", true);
			if(!$piwikUrl
				|| !Piwik_Common::isLookLikeUrl($piwikUrl))
			{
				$this->logFatalError("archive.php expects the argument --url to be set to your Piwik URL, for example: --url=http://example.org/piwik/ ", $backtrace = false);
			}
			// ensure there is a trailing slash
			if($piwikUrl[strlen($piwikUrl)-1] != '/')
			{
				$piwikUrl .= '/';
			}
		}
		if(Piwik_Config::getInstance()->General['force_ssl'] == 1)
		{
			$piwikUrl = str_replace('http://', 'https://', $piwikUrl);
		}
		$this->piwikUrl = $piwikUrl . "index.php";
	}
	
	
	/**
	 * Returns if the requested parameter is defined in the command line arguments.
	 * If $valuePossible is true, then a value is possibly set for this parameter, 
	 * ie. --force-timeout-for-periods=3600 would return 3600
	 * 
	 * @return true or the value (int,string) if set, false otherwise
	 */
	private function isParameterSet($parameter, $valuePossible = false)
	{
		if(!Piwik_Common::isPhpCliMode())
		{
			return false;
		}
		$parameters = array(
			"--$parameter",
			"-$parameter",
			$parameter
		);
		foreach($parameters as $parameter)
		{
			foreach($_SERVER['argv'] as $arg)
			{
				if( strpos($arg, $parameter) === 0)
				{
					if($valuePossible)
					{
						$parameterFound = $arg;
						if(($posEqual = strpos($parameterFound, '=')) !== false)
						{
							$return = substr($parameterFound, $posEqual+1);
							if($return !== false)
							{
								return $return;
							}
						}
					}
					return true;
				}
			}
		}
		return false;
	}
	
}

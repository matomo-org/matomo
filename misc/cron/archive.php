<?php
/**
 * Description
 * This script is a much optimized rewrite of archive.sh in PHP 
 * allowing for more flexibility and better performance when Piwik tracks thousands of websites.
 * 
 * What this script does:
 * - Fetches Super User token_auth from config file
 * - Calls API to get the list of all websites Ids with new visits since the last archive.php succesful run
 * - Calls API to get the list of segments to pre-process
 * The script then loops over these websites & segments and calls the API to pre-process these reports.
 * It does try to achieve Near real time for "daily" reports, processing them as often as possible.
 * 
 * Notes about the algorithm:
 * - To improve performance, API is called with date=last1 whenever possible, instead of last52 
 * - The script will only process (or re-process) reports for Current week / Current month  
 * 	 or Current year at most once per hour. 
 *   You can change this timeout as a parameter of the archive.php script.
 *   The concept is to archive daily report as often as possible, to stay near real time on "daily" reports,
 *   while allowing less real time weekly/monthly/yearly reporting. 
 */

/**
 * TODO/Ideas
 * - Process first all period=day, then all other periods (less important)
 * - Ensure script can only run once at a time
 * - Add "report last processed X s ago" in UI grey box "About"
 * - piwik.org update FAQ / online doc
 * - check that when ran as crontab, it will email errors when there is any
 * 
 */
define('PIWIK_INCLUDE_PATH', realpath('../../'));
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";
Piwik_FrontController::getInstance()->init();

$archiving = new Archiving;
$archiving->init();
$archiving->run();
$archiving->end();

class Archiving
{
	protected $piwikUrl = false;
	protected $token_auth = false;
	protected $processPeriodsMaximumEverySeconds = 3600;
	const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";
	
	protected $visits = 0;
	protected $requests = 0;

	/**
	 * By default, will process last 52 days/weeks/months/year.
	 * It will be overwritten by the number of days since last archiving ran until completion.
	 */
	protected $dateLast = 52; 
	
	private function log($m)
	{
//		echo $m . "\n";
		Piwik::log($m);
	}
	
	/**
	 * Issues a request to $url
	 */
	protected function request($url)
	{
		$url = $this->piwikUrl.$url;
//		$this->log($url);
		return trim(file_get_contents($url));
	}
	
	/**
	 * Displays script usage
	 */
	protected function usage()
	{
	    $this->log("Usage: {$_SERVER['argv'][0]} <hostname> [<current_periods_timeout>]");
	    $this->log("<hostname>: Piwik hostname eg. localhost, localhost/piwik");
	    $this->log("<current_periods_timeout>: Current week/month/year will be processed at most every <current_periods_timeout> seconds. Defaults to ".$this->processPeriodsMaximumEverySeconds);
	    $this->log("Description: this script will archive all reports (pre-process) for websites that have received new visits since the last succesful run");
	}
	
	public function init()
	{
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
		if ($_SERVER['argc'] != 2
			&& $_SERVER['argc'] != 3) {
			$this->usage();
		    exit;
		}
		if ($_SERVER['argc'] == 3) {
			if(!is_int($_SERVER['argc']))
			{
				$this->log("Expecting <current_periods_timeout> to be a number of seconds");
				exit;
			}
			$this->processPeriodsMaximumEverySeconds = $_SERVER['argv'][2];
			
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
			$this->log("WARNING: you should probably disable Browser archiving in Piwik UI > Settings > General Settings. See doc at: http://piwik.org/docs/setup-auto-archiving/");
		}
		$this->timeLastCompleted = Piwik_GetOption(self::OPTION_ARCHIVING_FINISHED_TS);
		
		$this->logSection("INIT");
		$this->piwikUrl ="http://{$_SERVER['argv'][1]}/index.php";
				
		// Fetching super user token_auth
		$login = Zend_Registry::get('config')->superuser->login;
		$password = Zend_Registry::get('config')->superuser->password;
		$this->token_auth = $this->request("?module=API&method=UsersManager.getTokenAuth&userLogin=$login&md5Password=$password&format=php&serialize=0");
		$this->log("Running as Super User: $login");
		
		// Fetching websites to process
		$result = $this->request("?module=API&method=SitesManager.getSitesIdWithVisits&token_auth=".$this->token_auth."&format=csv&convertToUnicode=0" . ($this->timeLastCompleted !== false ? "&timestamp=".$this->timeLastCompleted : ""));
		$this->websites = explode("\n", $result);
		if(!is_array($this->websites)
			|| $this->websites[0] == "No data available" ) {
			$this->websites = array();
		}
		$this->log(count($this->websites). " Websites with traffic since last run ". (!empty($this->websites) ? ": ".implode(", ", $this->websites) : ""));
		
		// Fetching segments to process
		$result = $this->request("?module=API&method=CoreAdminHome.getKnownSegmentsToArchive&token_auth=".$this->token_auth."&format=csv&convertToUnicode=0");
		$this->segments  = explode("\n", $result);
		// Remove value from segments
		unset($this->segments[array_search('value', $this->segments)]);
		$this->log("Segments to pre-process: ". implode(", ", $this->segments));
		
		$this->log("Notes");
		// Try and not request older data we know is already archived
		if($this->timeLastCompleted !== false)
		{
			$dateLast = time() - $this->timeLastCompleted;
			if($dateLast > 0)
			{
				$this->dateLast = floor($dateLast / 86400) + 1;
				$this->log("- Full archiving last executed ".Piwik::getPrettyTimeFromSeconds($dateLast, true, $isHtml = false)." ago, only requesting API with date=last".$this->dateLast);
			}
		}
		// Information about timeout
		$this->log("- Reports for today will be processed at most every ".Piwik_ArchiveProcessing::getTodayArchiveTimeToLive()." seconds. You can change this value in Piwik UI > Settings > General Settings");
		$this->log("- Reports for the current week/month/year will be refreshed at most every ".$this->processPeriodsMaximumEverySeconds." seconds");
	}

	/**
	 * Returns URL to process reports for the $idsite on a given period with no segment
	 */
	protected function getVisitsRequestUrl($idsite, $period)
	{
		return $this->piwikUrl."?module=API&method=VisitsSummary.getVisits&idSite=$idsite&period=$period&date=last".$this->dateLast."&format=php&token_auth=".$this->token_auth;
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
			$archivedPeriodsArchivesWebsite = 0;
		$timer = new Piwik_Timer;
		
		$this->logSection("START");
		$this->log("Starting Piwik reports archiving...");
		foreach ($this->websites as $idsite) 
		{
		    if ($idsite > 0) 
		    {
	            $url = $this->getVisitsRequestUrl($idsite, "day");
	            $ch = curl_init($url);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	            $response = curl_exec($ch);
	            $response = unserialize($response);
	            $visitsToday = end($response);
	            $this->requests++;
	            
		        $timerWebsite = new $timer;
	            if($visitsToday <= 0)
	            {
	            	$this->log("Skipping website, no visit today");
	            	break;
	            }
	            
	            $this->visits += $visitsToday;
	            $websitesWithVisitsSinceLastRun++;
	            $this->archiveVisitsAndSegments($idsite, "day");
	            
	            $lastRunForWebsite = Piwik_GetOption( $this->lastRunKey($idsite) );
	            $shouldArchivePeriods = (time() - $lastRunForWebsite) > $this->processPeriodsMaximumEverySeconds;
//	            $this->log("Archiving idsite = $idsite...");
        		// For period other than days, we only re-process the reports at most
	        	if(
	        		// 1) every $processPeriodsMaximumEverySeconds
	        		$shouldArchivePeriods
	        		// 2) OR always if script never executed for this website before
	        		|| $lastRunForWebsite === false)
        		{
			        foreach (array('week', 'month', 'year') as $period) 
			        {
	        			$this->archiveVisitsAndSegments($idsite, $period);
	        			
	        		}
        			// Record succesful run of this website archiving
        			Piwik_SetOption( $this->lastRunKey($idsite), time() );
        			$archivedPeriodsArchivesWebsite++;
		        }
		        else
		        {
		        	$skippedPeriodsArchivesWebsite++;
		        }
		        
		        Piwik::log("Archived idsite = $idsite, ". $timerWebsite);
		    }
		}
		
		$this->log("Done archiving!");
		
		$this->logSection("SUMMARY");
		$this->log("Total daily visits archived: ". $this->visits);
		$this->log("Archived today's report for $websitesWithVisitsSinceLastRun websites");
		// Fetching total websites
		Piwik::setUserIsSuperUser(true);
		$totalWebsites = Piwik_SitesManager_API::getInstance()->getAllSitesId();
		$totalWebsites = count($totalWebsites);
		$skipped = $totalWebsites - $websitesWithVisitsSinceLastRun;
		$this->log("Archived week/month/year for $archivedPeriodsArchivesWebsite websites. ");
		$this->log("Skipped $skippedPeriodsArchivesWebsite websites week/month/year archiving: existing reports are less than {$this->processPeriodsMaximumEverySeconds} seconds old");
		$this->log("Skipped $skipped websites: no new visit since the last script execution");
		$this->log("Total API requests: $this->requests");
		$this->log($timer);
		$this->logSection("SCHEDULED TASKS");
		$this->log("Starting Scheduled tasks... ");
		
		$this->request("?module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=".$this->token_auth);
		$this->log("done");
	}
	
	private function archiveVisitsAndSegments($idsite, $period)
	{
		$this->log("Archiving idsite = $idsite, period = $period...");
	    $aCurl = array();
		$mh = curl_multi_init();
		$url = $this->getVisitsRequestUrl($idsite, $period);
	    // already processed above for "day"
	    if($period != "day")
	    {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_multi_add_handle($mh, $ch);
			$aCurl[] = $ch;
			$this->requests++;
	    }
	    
	    foreach ($this->segments as $segment) {
			$ch = curl_init($url.'&segment='.urlencode($segment));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_multi_add_handle($mh, $ch);
			$aCurl[] = $ch;
			$this->requests++;
	    }
	    $running=null;
	    do {
			usleep(10000);
			curl_multi_exec($mh,$running);
	    } while ($running > 0);
	
	    foreach ($aCurl as $ch) {
	    	curl_multi_remove_handle($mh, $ch);
	    }
	    curl_multi_close($mh);
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
		// Logs the succesful script execution until completion
		Piwik_SetOption(self::OPTION_ARCHIVING_FINISHED_TS, time());
	}
}

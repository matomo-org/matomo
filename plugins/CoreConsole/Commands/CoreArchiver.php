<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CronArchive;
use Piwik\Log;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class CoreArchiver extends ConsoleCommand
{
    protected function configure()
    {
        $this->configureArchiveCommand($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getOption('url') ?: $input->getOption('piwik-domain');
        if (empty($url)) {
            throw new \InvalidArgumentException("The --url argument is not set. It should be set to your Piwik URL, for example: --url=http://example.org/piwik/.");
        }

        if (is_string($url) && $url && in_array($url, array('http://', 'https://'))) {
            // see https://github.com/piwik/piwik/issues/5180 and http://forum.piwik.org/read.php?2,115274
            throw new \InvalidArgumentException('No valid URL given. If you have specified a valid URL try --piwik-domain instead of --url');
        }

        if ($input->getOption('verbose')) {
            Log::getInstance()->setLogLevel(Log::VERBOSE);
        }

        $archiver = $this->makeArchiver($url, $input);

        try {
            $archiver->main();
        } catch (Exception $e) {
            $archiver->logFatalError($e->getMessage());
        }
    }

    // also used by another console command
    public static function makeArchiver($url, InputInterface $input)
    {
        $archiver = new CronArchive($url);

        $archiver->disableScheduledTasks = $input->getOption('disable-scheduled-tasks');
        $archiver->acceptInvalidSSLCertificate = $input->getOption("accept-invalid-ssl-certificate");
        $archiver->shouldArchiveAllSites = (bool) $input->getOption("force-all-websites");
        $archiver->shouldStartProfiler = (bool) $input->getOption("xhprof");
        $archiver->shouldArchiveSpecifiedSites = self::getSitesListOption($input, "force-idsites");
        $archiver->shouldSkipSpecifiedSites = self::getSitesListOption($input, "skip-idsites");
        $archiver->forceTimeoutPeriod = $input->getOption("force-timeout-for-periods");
        $archiver->shouldArchiveAllPeriodsSince = $input->getOption("force-all-periods");
        $archiver->restrictToDateRange = $input->getOption("force-date-range");

        $restrictToPeriods = $input->getOption("force-periods");
        $restrictToPeriods = explode(',', $restrictToPeriods);
        $archiver->restrictToPeriods = array_map('trim', $restrictToPeriods);

        $archiver->dateLastForced = $input->getOption('force-date-last-n');
        $archiver->concurrentRequestsPerWebsite = $input->getOption('concurrent-requests-per-website');

        return $archiver;
    }

    private static function getSitesListOption(InputInterface $input, $optionName)
    {
        return Site::getIdSitesFromIdSitesString($input->getOption($optionName));
    }

    // This is reused by another console command
    public static function configureArchiveCommand(ConsoleCommand $command)
    {
        $command->setName('core:archive');
        $command->setDescription("Runs the CLI archiver. It is an important tool for general maintenance and to keep Piwik very fast.");
        $command->setHelp("* It is recommended to run the script with the option --url=[piwik-server-url] only. Other options are not required.
  Try --piwik-domain if --url does not work for you.
* This script should be executed every hour via crontab, or as a daemon.
* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'),
  but it is recommended to run it via command line/CLI instead.
* If you have any suggestion about this script, please let the team know at feedback@piwik.org
* Enjoy!");
        $command->addOption('url', null, InputOption::VALUE_REQUIRED, "Mandatory option as an alternative to '--piwik-domain'. Must be set to the Piwik base URL.\nFor example: --url=http://analytics.example.org/ or --url=https://example.org/piwik/");
        $command->addOption('force-all-websites', null, InputOption::VALUE_NONE, "If specified, the script will trigger archiving on all websites.\nUse with --force-all-periods=[seconds] to also process those websites\nthat had visits in the last [seconds] seconds.");
        $command->addOption('force-all-periods', null, InputOption::VALUE_OPTIONAL, "Limits archiving to websites with some traffic in the last [seconds] seconds. \nFor example --force-all-periods=86400 will archive websites that had visits in the last 24 hours. \nIf [seconds] is not specified, all websites with visits in the last " . CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE . "\n seconds (" . round(CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE / 86400) . " days) will be archived.");
        $command->addOption('force-timeout-for-periods', null, InputOption::VALUE_OPTIONAL, "The current week/ current month/ current year will be processed at most every [seconds].\nIf not specified, defaults to " . CronArchive::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES . ".");
        $command->addOption('skip-idsites', null, InputOption::VALUE_OPTIONAL, 'If specified, archiving will be skipped for these websites (in case these website ids would have been archived).');
        $command->addOption('force-idsites', null, InputOption::VALUE_OPTIONAL, 'If specified, archiving will be processed only for these Sites Ids (comma separated)');
        $command->addOption('force-periods', null, InputOption::VALUE_OPTIONAL, "If specified, archiving will be processed only for these Periods (comma separated eg. day,week,month)");
        $command->addOption('force-date-last-n', null, InputOption::VALUE_REQUIRED, "This script calls the API with period=lastN. You can force the N in lastN by specifying this value.");
        $command->addOption('force-date-range', null, InputOption::VALUE_OPTIONAL, "If specified, archiving will be processed only for periods included in this date range. Format: YYYY-MM-DD,YYYY-MM-DD");
        $command->addOption('concurrent-requests-per-website', null, InputOption::VALUE_OPTIONAL, "When processing a website and its segments, number of requests to process in parallel", CronArchive::MAX_CONCURRENT_API_REQUESTS);
        $command->addOption('disable-scheduled-tasks', null, InputOption::VALUE_NONE, "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).");
        $command->addOption('accept-invalid-ssl-certificate', null, InputOption::VALUE_NONE, "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be useful if you specified --url=https://... or if you are using Piwik with force_ssl=1");
        $command->addOption('xhprof', null, InputOption::VALUE_NONE, "Enables XHProf profiler for this archive.php run. Requires XHPRof (see tests/README.xhprof.md).");
    }
}
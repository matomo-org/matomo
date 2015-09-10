<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CronArchive;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CoreArchiver extends ConsoleCommand
{
    protected function configure()
    {
        $this->configureArchiveCommand($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $archiver = self::makeArchiver($input->getOption('url'), $input);
        $archiver->main();
    }

    // also used by another console command
    public static function makeArchiver($url, InputInterface $input)
    {
        $archiver = new CronArchive();

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

        $archiver->disableSegmentsArchiving = $input->getOption('skip-all-segments');

        $segmentIds = $input->getOption('force-idsegments');
        $segmentIds = explode(',', $segmentIds);
        $segmentIds = array_map('trim', $segmentIds);
        $archiver->setSegmentsToForceFromSegmentIds($segmentIds);

        $archiver->setUrlToPiwik($url);

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
        $command->setHelp("* It is recommended to run the script without any option.
* This script should be executed every hour via crontab, or as a daemon.
* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'),
  but it is recommended to run it via command line/CLI instead.
* If you have any suggestion about this script, please let the team know at feedback@piwik.org
* Enjoy!");
        $command->addOption('url', null, InputOption::VALUE_REQUIRED,
            "Forces the value of this option to be used as the URL to Piwik. \nIf your system does not support"
            . " archiving with CLI processes, you may need to set this in order for the archiving HTTP requests to use"
            . " the desired URLs.");
        $command->addOption('force-all-websites', null, InputOption::VALUE_NONE,
            "If specified, the script will trigger archiving on all websites.\nUse with --force-all-periods=[seconds] "
            . "to also process those websites that had visits in the last [seconds] seconds.\nLaunching several processes"
            . " with this option will make them share the list of sites to process.");
        $command->addOption('force-all-periods', null, InputOption::VALUE_OPTIONAL,
            "Limits archiving to websites with some traffic in the last [seconds] seconds. \nFor example "
            . "--force-all-periods=86400 will archive websites that had visits in the last 24 hours. \nIf [seconds] is "
            . "not specified, all websites with visits in the last " . CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE
            . " seconds (" . round(CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE / 86400) . " days) will be archived.");
        $command->addOption('force-timeout-for-periods', null, InputOption::VALUE_OPTIONAL,
            "The current week/ current month/ current year will be processed at most every [seconds].\nIf not "
            . "specified, defaults to " . CronArchive::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES . ".");
        $command->addOption('skip-idsites', null, InputOption::VALUE_OPTIONAL,
            'If specified, archiving will be skipped for these websites (in case these website ids would have been archived).');
        $command->addOption('skip-all-segments', null, InputOption::VALUE_NONE,
            'If specified, all segments will be skipped during archiving.');
        $command->addOption('force-idsites', null, InputOption::VALUE_OPTIONAL,
            'If specified, archiving will be processed only for these Sites Ids (comma separated)');
        $command->addOption('force-periods', null, InputOption::VALUE_OPTIONAL,
            "If specified, archiving will be processed only for these Periods (comma separated eg. day,week,month,year,range)");
        $command->addOption('force-date-last-n', null, InputOption::VALUE_REQUIRED,
            "This script calls the API with period=lastN. You can force the N in lastN by specifying this value.");
        $command->addOption('force-date-range', null, InputOption::VALUE_OPTIONAL,
            "If specified, archiving will be processed only for periods included in this date range. Format: YYYY-MM-DD,YYYY-MM-DD");
        $command->addOption('force-idsegments', null, InputOption::VALUE_REQUIRED,
            'If specified, only these segments will be processed (if the segment should be applied to a site in the first place).'
            . "\nSpecify stored segment IDs, not the segments themselves, eg, 1,2,3. "
            . "\nNote: if identical segments exist w/ different IDs, they will both be skipped, even if you only supply one ID.");
        $command->addOption('concurrent-requests-per-website', null, InputOption::VALUE_OPTIONAL,
            "When processing a website and its segments, number of requests to process in parallel", CronArchive::MAX_CONCURRENT_API_REQUESTS);
        $command->addOption('disable-scheduled-tasks', null, InputOption::VALUE_NONE,
            "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).");
        $command->addOption('accept-invalid-ssl-certificate', null, InputOption::VALUE_NONE,
            "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be "
            . "useful if you specified --url=https://... or if you are using Piwik with force_ssl=1");
    }
}

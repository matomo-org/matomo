<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        if($input->getOption('force-date-last-n')) {
            $message = '"force-date-last-n" is deprecated. Please use the "process_new_segments_from" INI configuration option instead.';
            $output->writeln('<comment>' . $message .'</comment>');
        }

        $archiver = self::makeArchiver($input->getOption('url'), $input);
        $archiver->main();
    }

    // also used by another console command
    public static function makeArchiver($url, InputInterface $input)
    {
        $archiver = new CronArchive();

        $archiver->disableScheduledTasks = $input->getOption('disable-scheduled-tasks');
        $archiver->acceptInvalidSSLCertificate = $input->getOption("accept-invalid-ssl-certificate");
        $archiver->shouldStartProfiler = (bool) $input->getOption("xhprof");
        $archiver->shouldArchiveSpecifiedSites = self::getSitesListOption($input, "force-idsites");
        $archiver->shouldSkipSpecifiedSites = self::getSitesListOption($input, "skip-idsites");
        $archiver->phpCliConfigurationOptions = $input->getOption("php-cli-options");
        $archiver->concurrentRequestsPerWebsite = $input->getOption('concurrent-requests-per-website');
        $archiver->maxConcurrentArchivers = $input->getOption('concurrent-archivers');
        $archiver->shouldArchiveAllSites = $input->getOption('force-all-websites');
        $archiver->maxSitesToProcess = $input->getOption('max-websites-to-process');
        $archiver->maxArchivesToProcess = $input->getOption('max-archives-to-process');
        $archiver->setUrlToPiwik($url);

        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setDisableSegmentsArchiving($input->getOption('skip-all-segments'));
        $archiveFilter->setRestrictToDateRange($input->getOption("force-date-range"));
        $archiveFilter->setRestrictToPeriods($input->getOption("force-periods"));
        $archiveFilter->setSkipSegmentsForToday($input->getOption('skip-segments-today'));
        $archiveFilter->setForceReport($input->getOption('force-report'));

        $segmentIds = $input->getOption('force-idsegments');
        if (!empty($segmentIds)) {
            $segmentIds = explode(',', $segmentIds);
            $segmentIds = array_map('trim', $segmentIds);
        } else {
            $segmentIds = [];
        }
        $archiveFilter->setSegmentsToForceFromSegmentIds($segmentIds);

        $archiver->setArchiveFilter($archiveFilter);

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
* If you have any suggestion about this script, please let the team know at feedback@matomo.org
* Enjoy!");
        $command->addOption('url', null, InputOption::VALUE_REQUIRED,
            "Forces the value of this option to be used as the URL to Piwik. \nIf your system does not support"
            . " archiving with CLI processes, you may need to set this in order for the archiving HTTP requests to use"
            . " the desired URLs.");
        $command->addOption('skip-idsites', null, InputOption::VALUE_OPTIONAL,
            'If specified, archiving will be skipped for these websites (in case these website ids would have been archived).');
        $command->addOption('skip-all-segments', null, InputOption::VALUE_NONE,
            'If specified, all segments will be skipped during archiving.');
        $command->addOption('force-idsites', null, InputOption::VALUE_OPTIONAL,
            'If specified, archiving will be processed only for these Sites Ids (comma separated)');
        $command->addOption('skip-segments-today', null, InputOption::VALUE_NONE,
            'If specified, segments will be only archived for yesterday, but not today. If the segment was created or changed recently, then it will still be archived for today and the setting will be ignored for this segment.');
        $command->addOption('force-periods', null, InputOption::VALUE_OPTIONAL,
            "If specified, archiving will be processed only for these Periods (comma separated eg. day,week,month,year,range)");
        $command->addOption('force-date-last-n', null, InputOption::VALUE_OPTIONAL,
            "Deprecated. Please use the \"process_new_segments_from\" INI configuration option instead.");
        $command->addOption('force-date-range', null, InputOption::VALUE_OPTIONAL,
            "If specified, archiving will be processed only for periods included in this date range. Format: YYYY-MM-DD,YYYY-MM-DD");
        $command->addOption('force-idsegments', null, InputOption::VALUE_REQUIRED,
            'If specified, only these segments will be processed (if the segment should be applied to a site in the first place).'
            . "\nSpecify stored segment IDs, not the segments themselves, eg, 1,2,3. "
            . "\nNote: if identical segments exist w/ different IDs, they will both be skipped, even if you only supply one ID.");
        $command->addOption('concurrent-requests-per-website', null, InputOption::VALUE_OPTIONAL,
            "When processing a website and its segments, number of requests to process in parallel", CronArchive::MAX_CONCURRENT_API_REQUESTS);
        $command->addOption('concurrent-archivers', null, InputOption::VALUE_OPTIONAL,
            "The number of max archivers to run in parallel. Depending on how you start the archiver as a cronjob, you may need to double the amount of archivers allowed if the same process appears twice in the `ps ex` output.", false);
        $command->addOption('max-websites-to-process', null, InputOption::VALUE_REQUIRED,
            "Maximum number of websites to process during a single execution of the archiver. Can be used to limit the process lifetime e.g. to avoid increasing memory usage.");
        $command->addOption('max-archives-to-process', null, InputOption::VALUE_REQUIRED,
            "Maximum number of archives to process during a single execution of the archiver. Can be used to limit the process lifetime e.g. to avoid increasing memory usage.");
        $command->addOption('disable-scheduled-tasks', null, InputOption::VALUE_NONE,
            "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).");
        $command->addOption('accept-invalid-ssl-certificate', null, InputOption::VALUE_NONE,
            "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be "
            . "useful if you specified --url=https://... or if you are using Piwik with force_ssl=1");
        $command->addOption('php-cli-options', null, InputOption::VALUE_OPTIONAL, 'Forwards the PHP configuration options to the PHP CLI command. For example "-d memory_limit=8G". Note: These options are only applied if the archiver actually uses CLI and not HTTP.', $default = '');
        $command->addOption('force-all-websites', null, InputOption::VALUE_NONE, 'Force archiving all websites.');
        $command->addOption('force-report', null, InputOption::VALUE_OPTIONAL, 'If specified, only processes invalidations for a specific report in a specific plugin. Value must be in the format of "MyPlugin.myReport".');
    }
}

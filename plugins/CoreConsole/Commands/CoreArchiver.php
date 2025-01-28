<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CronArchive;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;

class CoreArchiver extends ConsoleCommand
{
    protected function configure()
    {
        $this->configureArchiveCommand($this);
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        if ($input->getOption('force-date-last-n')) {
            $message = '"force-date-last-n" is deprecated. Please use the "process_new_segments_from" INI configuration option instead.';
            $output->writeln('<comment>' . $message . '</comment>');
        }

        $archiver = $this->makeArchiver($input->getOption('url'));
        $archiver->main();

        return self::SUCCESS;
    }

    protected function makeArchiver($url)
    {
        $input = $this->getInput();
        $archiver = new CronArchive();

        $archiver->disableScheduledTasks = $input->getOption('disable-scheduled-tasks');
        $archiver->acceptInvalidSSLCertificate = $input->getOption("accept-invalid-ssl-certificate");
        $archiver->shouldStartProfiler = (bool) $input->getOption("xhprof");
        $archiver->shouldArchiveSpecifiedSites = $this->getSitesListOption("force-idsites");
        $archiver->shouldSkipSpecifiedSites = $this->getSitesListOption("skip-idsites");
        $archiver->phpCliConfigurationOptions = $input->getOption("php-cli-options");
        $archiver->concurrentRequestsPerWebsite = $input->getOption('concurrent-requests-per-website');
        $archiver->maxConcurrentArchivers = $input->getOption('concurrent-archivers');
        $archiver->shouldArchiveAllSites = $input->getOption('force-all-websites');
        $archiver->maxSitesToProcess = $input->getOption('max-websites-to-process');
        $archiver->maxArchivesToProcess = $input->getOption('max-archives-to-process');
        $archiver->stopProcessingAfter = $input->getOption('stop-processing-after');
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

    private function getSitesListOption($optionName)
    {
        return Site::getIdSitesFromIdSitesString($this->getInput()->getOption($optionName));
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
        $command->addRequiredValueOption(
            'url',
            null,
            "Forces the value of this option to be used as the URL to Piwik. \nIf your system does not support"
            . " archiving with CLI processes, you may need to set this in order for the archiving HTTP requests to use"
            . " the desired URLs."
        );
        $command->addOptionalValueOption(
            'skip-idsites',
            null,
            'If specified, archiving will be skipped for these websites (in case these website ids would have been archived).'
        );
        $command->addNoValueOption(
            'skip-all-segments',
            null,
            'If specified, all segments will be skipped during archiving.'
        );
        $command->addOptionalValueOption(
            'force-idsites',
            null,
            'If specified, archiving will be processed only for these Sites Ids (comma separated)'
        );
        $command->addNoValueOption(
            'skip-segments-today',
            null,
            'If specified, segments will be only archived for yesterday, but not today. If the segment was created or changed recently, then it will still be archived for today and the setting will be ignored for this segment.'
        );
        $command->addOptionalValueOption(
            'force-periods',
            null,
            "If specified, archiving will be processed only for these Periods (comma separated eg. day,week,month,year,range)"
        );
        $command->addOptionalValueOption(
            'force-date-last-n',
            null,
            "Deprecated. Please use the \"process_new_segments_from\" INI configuration option instead."
        );
        $command->addOptionalValueOption(
            'force-date-range',
            null,
            "If specified, archiving will be processed only for periods included in this date range. Format: YYYY-MM-DD,YYYY-MM-DD"
        );
        $command->addRequiredValueOption(
            'force-idsegments',
            null,
            'If specified, only these segments will be processed (if the segment should be applied to a site in the first place).'
            . "\nSpecify stored segment IDs, not the segments themselves, eg, 1,2,3. "
            . "\nNote: if identical segments exist w/ different IDs, they will both be skipped, even if you only supply one ID."
        );
        $command->addOptionalValueOption(
            'concurrent-requests-per-website',
            null,
            "When processing a website and its segments, number of requests to process in parallel",
            CronArchive::MAX_CONCURRENT_API_REQUESTS
        );
        $command->addOptionalValueOption(
            'concurrent-archivers',
            null,
            "The number of max archivers to run in parallel. Depending on how you start the archiver as a cronjob, you may need to double the amount of archivers allowed if the same process appears twice in the `ps ex` output.",
            3
        );
        $command->addRequiredValueOption(
            'max-websites-to-process',
            null,
            "Maximum number of websites to process during a single execution of the archiver. Can be used to limit the process lifetime e.g. to avoid increasing memory usage."
        );
        $command->addRequiredValueOption(
            'max-archives-to-process',
            null,
            "Maximum number of archives to process during a single execution of the archiver. Can be used to limit the process lifetime e.g. to avoid increasing memory usage."
        );
        $command->addOptionalValueOption(
            'stop-processing-after',
            null,
            "Number of seconds how long a job is allowed to start new archiving processes. When limit is reached job will wrap up after finishing current processes.",
            60 * 60 * 24 // 24 hours, to ensure archiving is started once a day
        );
        $command->addNoValueOption(
            'disable-scheduled-tasks',
            null,
            "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.)."
        );
        $command->addNoValueOption(
            'accept-invalid-ssl-certificate',
            null,
            "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be "
            . "useful if you specified --url=https://... or if you are using Piwik with force_ssl=1"
        );
        $command->addOptionalValueOption('php-cli-options', null, 'Forwards the PHP configuration options to the PHP CLI command. For example "-d memory_limit=8G". Note: These options are only applied if the archiver actually uses CLI and not HTTP.', $default = '');
        $command->addNoValueOption('force-all-websites', null, 'Force archiving all websites.');
        $command->addOptionalValueOption('force-report', null, 'If specified, only processes invalidations for a specific report in a specific plugin. Value must be in the format of "MyPlugin.myReport".');
    }
}

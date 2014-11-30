<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CronArchive;
use Piwik\Jobs\Impl\CliProcessor;
use Piwik\Jobs\Impl\DistributedJobsQueue;
use Piwik\Jobs\Queue;
use Piwik\Log;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Piwik\Jobs\Helper as JobsHelper;
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
        $url = $input->getOption('piwik-domain');
        if (is_string($url)
            && $url
            && in_array($url, array('http://', 'https://'))
        ) {
            // see https://github.com/piwik/piwik/issues/5180 and http://forum.piwik.org/read.php?2,115274
            throw new \InvalidArgumentException('No valid URL given. If you have specified a valid URL try --piwik-domain instead of --url');
        }

        if ($input->getOption('verbose')) {
            Log::getInstance()->setLogLevel(Log::VERBOSE);
        }

        $archiver = $this->makeArchiver($input);

        try {
            $archiver->run();
        } catch (Exception $e) {
            $archiver->algorithmLogger->logError($e->getMessage()); // TODO: will result in two errors if logFatalError called
        }
    }

    // also used by another console command
    public static function makeArchiver(InputInterface $input)
    {
        $options = new CronArchive\AlgorithmOptions();
        $options->disableScheduledTasks = $input->getOption('disable-scheduled-tasks');
        $options->shouldArchiveAllSites = (bool) $input->getOption("force-all-websites");
        $options->shouldStartProfiler = (bool) $input->getOption("xhprof");
        $options->shouldArchiveSpecifiedSites = self::getSitesListOption($input, "force-idsites");
        $options->shouldSkipSpecifiedSites = self::getSitesListOption($input, "skip-idsites");
        $options->forceTimeoutPeriod = $input->getOption("force-timeout-for-periods");
        $options->shouldArchiveAllPeriodsSince = $input->getOption("force-all-periods");
        $options->restrictToDateRange = $input->getOption("force-date-range");
        $options->acceptInvalidSSLCertificate = $input->getOption("accept-invalid-ssl-certificate");

        $restrictToPeriods = $input->getOption("force-periods");
        $restrictToPeriods = explode(',', $restrictToPeriods);
        $options->restrictToPeriods = array_map('trim', $restrictToPeriods);

        $options->dateLastForced = $input->getOption('force-date-last-n');

        $options->testmode = $input->getOption('testmode');

        $queueName = $input->getOption('queue');
        $queue = empty($queueName) ? self::makeDefaultQueue() : JobsHelper::getNamedQueue($queueName);

        $processorName = $input->getOption('processor');
        $processor = empty($processorName)
                   ? self::makeDefaultCliProcessor($input, $queue, $options->acceptInvalidSSLCertificate)
                   : JobsHelper::getNamedProcessor($processorName);

        return new CronArchive($options, $queueName, $processor);
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
        $command->setHelp("* It is recommended to run the script with no options. Other options are not required.
  Use --piwik-domain if you are managing multiple piwik instances w/ a single codebase.
* This script should be executed every hour via crontab, or as a daemon.
* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'),
  but it is recommended to run it via command line/CLI instead. [Use the misc/cron/archive.php script for this.
* If you have any suggestion about this script, please let the team know at feedback@piwik.org
* Enjoy!");
        $command->addOption('url', null, InputOption::VALUE_REQUIRED, "Deprecated.");
        $command->addOption('max-processes', null, InputOption::VALUE_REQUIRED, "If specified, sets the maximum number of allowed child processes to use when archiving visits. By default this is set to " . CliProcessor::DEFAULT_MAX_SPAWNED_PROCESS_COUNT . ". If a custom processoris used, this option is ignored.");
        $command->addOption('queue', null, InputOption::VALUE_REQUIRED, "The un-prefixed name of a queue defined in DI config to use for scheduling jobs. If, for example, 'redis_queue' is used, then it is expected that a Queue object is defined in DI config with the name 'jobs.queue.redis_queue'.");
        $command->addOption('processor', null, InputOption::VALUE_REQUIRED, "The un-prefixed name of a job processor defined in DI config to use for processing jobs. If for example, 'gearman_processor' is used, then it is expected that a Processor object is defined in DI config with the name 'jobs.processor.gearman_processor'.");
        $command->addOption('force-all-websites', null, InputOption::VALUE_NONE, "If specified, the script will trigger archiving on all websites.\nUse with --force-all-periods=[seconds] to also process those websites\nthat had visits in the last [seconds] seconds.");
        $command->addOption('force-all-periods', null, InputOption::VALUE_OPTIONAL, "Limits archiving to websites with some traffic in the last [seconds] seconds. \nFor example --force-all-periods=86400 will archive websites that had visits in the last 24 hours. \nIf [seconds] is not specified, all websites with visits in the last " . CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE . "\n seconds (" . round(CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE / 86400) . " days) will be archived.");
        $command->addOption('force-timeout-for-periods', null, InputOption::VALUE_OPTIONAL, "The current week/ current month/ current year will be processed at most every [seconds].\nIf not specified, defaults to " . CronArchive\AlgorithmRules::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES . ".");
        $command->addOption('skip-idsites', null, InputOption::VALUE_OPTIONAL, 'If specified, archiving will be skipped for these websites (in case these website ids would have been archived).');
        $command->addOption('force-idsites', null, InputOption::VALUE_OPTIONAL, 'If specified, archiving will be processed only for these Sites Ids (comma separated)');
        $command->addOption('force-periods', null, InputOption::VALUE_OPTIONAL, "If specified, archiving will be processed only for these Periods (comma separated eg. day,week,month)");
        $command->addOption('force-date-last-n', null, InputOption::VALUE_REQUIRED, "This script calls the API with period=lastN. You can force the N in lastN by specifying this value.");
        $command->addOption('force-date-range', null, InputOption::VALUE_OPTIONAL, "If specified, archiving will be processed only for periods included in this date range. Format: YYYY-MM-DD,YYYY-MM-DD");
        $command->addOption('disable-scheduled-tasks', null, InputOption::VALUE_NONE, "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).");
        $command->addOption('accept-invalid-ssl-certificate', null, InputOption::VALUE_NONE, "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be useful if you specified --url=https://... or if you are using Piwik with force_ssl=1");
        $command->addOption('xhprof', null, InputOption::VALUE_NONE, "Enables XHProf profiler for this archive.php run. Requires XHPRof (see tests/README.xhprof.md).");
        $command->addOption('testmode', null, InputOption::VALUE_NONE, "Used during tests.");
    }

    private static function makeDefaultCliProcessor(InputInterface $input, Queue $queue, $acceptInvalidSSLCertificate)
    {
        $maxProcesses = ((int) $input->getOption('max-processes')) ?: CliProcessor::DEFAULT_MAX_SPAWNED_PROCESS_COUNT;
        return new CliProcessor($queue, $maxProcesses, CliProcessor::DEFAULT_SLEEP_TIME, $acceptInvalidSSLCertificate);
    }

    private static function makeDefaultQueue()
    {
        return new DistributedJobsQueue();
    }
}
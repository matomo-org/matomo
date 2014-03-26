<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CronArchive;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CoreArchiver extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:archive');
        $this->setDescription("Runs the CLI archiver. It usually runs as a cron and is a useful tool for general maintenance, and pre-process reports for a Fast dashboard rendering.");
        $this->setHelp("* It is recommended to run the script with the option --piwik-domain=[piwik-server-url] only. Other options are not required.
* This script should be executed every hour via crontab, or as a daemon.
* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'),
  but it is recommended to run it via command line/CLI instead.
* If you have any suggestion about this script, please let the team know at hello@piwik.org
* Enjoy!");
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, "Mandatory option as an alternative to '--piwik-domain'. Must be set to the Piwik base URL.\nFor example: --url=http://analytics.example.org/ or --url=https://example.org/piwik/");
        $this->addOption('force-all-websites', null, InputOption::VALUE_NONE, "If specified, the script will trigger archiving on all websites and all past dates.\nYou may use --force-all-periods=[seconds] to trigger archiving on those websites\nthat had visits in the last [seconds] seconds.");
        $this->addOption('force-all-periods', null, InputOption::VALUE_OPTIONAL, "Limits archiving to websites with some traffic in the last [seconds] seconds. \nFor example --force-all-periods=86400 will archive websites that had visits in the last 24 hours. \nIf [seconds] is not specified, all websites with visits in the last " . CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE . "\n seconds (" . round( CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE/86400 ) ." days) will be archived.");
        $this->addOption('force-timeout-for-periods', null, InputOption::VALUE_OPTIONAL, "The current week/ current month/ current year will be processed at most every [seconds].\nIf not specified, defaults to ". CronArchive::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES.".");
        $this->addOption('force-date-last-n', null, InputOption::VALUE_REQUIRED, "This script calls the API with period=lastN. You can force the N in lastN by specifying this value.");
        $this->addOption('force-idsites', null,InputOption::VALUE_REQUIRED, "Restricts archiving to the specified website IDs, comma separated list.");
        $this->addOption('skip-idsites', null, InputOption::VALUE_REQUIRED, "If the specified websites IDs were to be archived, skip them instead.");
        $this->addOption('disable-scheduled-tasks', null, InputOption::VALUE_NONE, "Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).");
        $this->addOption('xhprof', null, InputOption::VALUE_NONE, "Enables XHProf profiler for this archive.php run. Requires XHPRof (see tests/README.xhprof.md).");
        $this->addOption('accept-invalid-ssl-certificate', null, InputOption::VALUE_NONE, "It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!\nIt can be useful if you specified --url=https://... or if you are using Piwik with force_ssl=1");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('piwik-domain') && !$input->getOption('url')) {
            $_SERVER['argv'][] = '--url=' . $input->getOption('piwik-domain');
        }

        $this->initEnv();

        $archiving = new CronArchive();
        try {
            $archiving->init();
            $archiving->run();
            $archiving->runScheduledTasks();
            $archiving->end();
        } catch (\Exception $e) {
            $archiving->logFatalError($e->getMessage());
        }
    }

    private function initEnv()
    {
        if (!defined('PIWIK_ENABLE_ERROR_HANDLER')) {
            define('PIWIK_ENABLE_ERROR_HANDLER', false);
        }

        if (!defined('PIWIK_ENABLE_SESSION_START')) {
            define('PIWIK_ENABLE_SESSION_START', false);
        }

        if (!defined('PIWIK_ENABLE_DISPATCH')) {
            define('PIWIK_ENABLE_DISPATCH', false);
        }
    }
}
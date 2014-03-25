<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CliMulti;
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
        $this->setDescription('Runs the CLI archiver');
        $this->addArgument('config', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Any parameters supported by the CronArchiver. Eg ./console core:archive url=http://example.org/piwik', array());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class WatchLog extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('log:watch');
        $this->setDescription('Outputs the last parts of the log files and follows as the log file grows. Does not work on Windows');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = sprintf('%s/tmp/logs/', PIWIK_DOCUMENT_ROOT);
        $path = SettingsPiwik::rewriteTmpPathWithInstanceId($path);
        $cmd = sprintf('tail -f %s*.log', $path);

        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}

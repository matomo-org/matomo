<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
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
        $path = StaticContainer::get('path.tmp') . '/logs/';
        $cmd = sprintf('tail -f %s*.log', $path);

        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}

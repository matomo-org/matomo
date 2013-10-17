<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
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
        $cmd = sprintf('tail -f %s/tmp/logs/*.log', PIWIK_DOCUMENT_ROOT);

        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}

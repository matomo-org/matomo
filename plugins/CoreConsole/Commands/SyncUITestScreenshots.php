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

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class SyncUITestScreenshots extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('development:sync-ui-test-screenshots');
        $this->setDescription('This command is intended for Piwik core developers. It copies all processed screenshot tests on Travis to the expected screenshot directory.');
        $this->addArgument('buildnumber', InputArgument::REQUIRED, 'Travis build number you want to sync');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildnumber = $input->getArgument('buildnumber');

        if (empty($buildnumber)) {
            throw new \InvalidArgumentException('Missing build number.');
        }

        $target = PIWIK_DOCUMENT_ROOT . '/tests/PHPUnit/UI/expected-ui-screenshots';

        $cmd = sprintf('wget -r --level=0 --no-parent -m -nH --cut-dirs=3 -p -erobots=off -P "%s" -A *.png http://builds-artifacts.piwik.org/ui-tests.master/%s/processed-ui-screenshots', $target, $buildnumber);
        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);

        $cmd = sprintf('rm -rf %s/Morpheus', $target);
        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}

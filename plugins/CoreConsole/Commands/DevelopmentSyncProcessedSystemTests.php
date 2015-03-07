<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Decompress\Tar;
use Piwik\Development;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevelopmentSyncProcessedSystemTests extends ConsoleCommand
{
    private $targetDir = 'tests/PHPUnit/System/processed';

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('development:sync-system-test-processed');
        $this->setDescription('For Piwik core devs. Copies processed system tests from travis artifacts to ' . $this->targetDir);
        $this->addOption('branch', null, InputOption::VALUE_REQUIRED, 'The branch the tests were running on', 'master');
        $this->addArgument('buildnumber', InputArgument::REQUIRED, 'Travis build number you want to sync.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $branch      = $input->getOption('branch');
        $buildNumber = $input->getArgument('buildnumber');
        $targetDir   = PIWIK_INCLUDE_PATH . '/' . dirname($this->targetDir);
        $tmpDir      = StaticContainer::get('path.tmp');

        $this->validate($buildNumber, $targetDir, $tmpDir);

        $filename = sprintf('processed.%s.tar.bz2', $buildNumber);
        $urlBase  = sprintf('http://builds-artifacts.piwik.org/%s/%s/%s', $branch, $buildNumber, $filename);
        $tests    = Http::sendHttpRequest($urlBase, $timeout = 120);

        $tarFile = $tmpDir . $filename;
        file_put_contents($tarFile, $tests);

        $tar = new Tar($tarFile, 'bz2');
        $tar->extract($targetDir);

        $this->writeSuccessMessage($output, array(
            'All processed system test results were copied to <comment>' . $this->targetDir . '</comment>',
            'Compare them with the expected test results and commit them if needed.'
        ));

        unlink($tarFile);
    }

    private function validate($buildNumber, $targetDir, $tmpDir)
    {
        if (empty($buildNumber)) {
            throw new \InvalidArgumentException('Missing build number.');
        }

        if (!is_writable($targetDir)) {
            throw new \RuntimeException('Target dir is not writable: ' . $targetDir);
        }

        if (!is_writable($tmpDir)) {
            throw new \RuntimeException('Tempdir is not writable: ' . $tmpDir);
        }
    }
}

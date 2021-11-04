<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Matomo\Decompress\Tar;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevelopmentSyncProcessedSystemTests extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('development:sync-system-test-processed');
        $this->setDescription('For Piwik core devs. Copies processed system tests from travis artifacts to local processed directories');
        $this->addArgument('buildnumber', InputArgument::REQUIRED, 'Travis build number you want to sync, eg "14820".');
        $this->addOption('expected', 'e', InputOption::VALUE_NONE, 'If given file will be copied in expected directories instead of processed');
        $this->addOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'Repository name you want to sync screenshots for.', 'matomo-org/matomo');
        $this->addOption('http-user', '', InputOption::VALUE_OPTIONAL, 'the HTTP AUTH username (for premium plugins where artifacts are protected)');
        $this->addOption('http-password', '', InputOption::VALUE_OPTIONAL, 'the HTTP AUTH password (for premium plugins where artifacts are protected)');
        $this->addOption('plugin', 'p', InputOption::VALUE_OPTIONAL, 'Name of the plugin the files shall be synced to');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateCoreFiles($input, $output);

        if ($input->getOption('repository') === 'matomo-org/matomo') {
            $this->updatePluginsFiles($input, $output);
        }
    }

    protected function updateCoreFiles(InputInterface $input, OutputInterface $output)
    {
        $buildNumber = $input->getArgument('buildnumber');
        $expected    = $input->getOption('expected');
        $targetDir   = sprintf(PIWIK_INCLUDE_PATH . '/tests/PHPUnit/System/%s', $expected ? 'expected' : 'processed');
        $repository = $input->getOption('repository');

        if ($input->getOption('plugin')) {
            $targetDir   = sprintf(PIWIK_INCLUDE_PATH . '/plugins/%s/tests/System/%s/', $input->getOption('plugin'), $expected ? 'expected' : 'processed');
        } else if (preg_match('/plugin-([a-z0-9]{3,40})$/i', $repository, $match)) {
            $targetDir   = sprintf(PIWIK_INCLUDE_PATH . '/plugins/%s/tests/System/%s/', $match[1], $expected ? 'expected' : 'processed');
        }

        $tmpDir      = StaticContainer::get('path.tmp') . '/';

        $this->validate($buildNumber, $targetDir, $tmpDir);

        if (Common::stringEndsWith($buildNumber, '.1')) {
            // eg make '14820.1' to '14820' to be backwards compatible
            $buildNumber = substr($buildNumber, 0, -2);
        }

        $httpUser = $input->getOption('http-user');
        $httpPassword = $input->getOption('http-password');

        $filename = sprintf('system.%s.tar.bz2', $buildNumber);
        $urlBase  = sprintf('https://builds-artifacts.matomo.org/%s/%s', $repository, $filename);
        $tests    = Http::sendHttpRequest($urlBase, $timeout = 120,
            $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'GET',
            $httpUser,
            $httpPassword);

        $tarFile = $tmpDir . $filename;
        file_put_contents($tarFile, $tests);

        $tar = new Tar($tarFile, 'bz2');

        if ($tar->extract($targetDir)) {
            $this->writeSuccessMessage($output, array(
                'All processed system test results were copied to <comment>' . $targetDir . '</comment>',
                'Compare them with the expected test results and commit them if needed.'
            ));
        } else {
            $output->write('<error>' . $tar->errorInfo() . '.</error>');
            $output->writeln('<error> Check that you have the PHP bz2 extension installed and try again.');
        }

        unlink($tarFile);
    }


    protected function updatePluginsFiles(InputInterface $input, OutputInterface $output)
    {
        $buildNumber = $input->getArgument('buildnumber');
        $expected    = $input->getOption('expected');
        $targetDir   = sprintf(PIWIK_INCLUDE_PATH . '/plugins/%%s/tests/System/%s/', $expected ? 'expected' : 'processed');
        $tmpDir      = StaticContainer::get('path.tmp') . '/';

        if (Common::stringEndsWith($buildNumber, '.1')) {
            // eg make '14820.1' to '14820' to be backwards compatible
            $buildNumber = substr($buildNumber, 0, -2);
        }

        $filename = sprintf('system.plugin.%s.tar.bz2', $buildNumber);
        $urlBase  = sprintf('https://builds-artifacts.matomo.org/matomo-org/matomo/%s', $filename);
        $tests    = Http::sendHttpRequest($urlBase, $timeout = 120);

        $tarFile = $tmpDir . $filename;
        file_put_contents($tarFile, $tests);

        $tar = new Tar($tarFile, 'bz2');

        $extractionTarget = $tmpDir . '/artifacts';

        Filesystem::mkdir($extractionTarget);

        $success = $tar->extract($extractionTarget);
        if (! $success) {
            $output->write('<error>' . $tar->errorInfo() . '.</error>');
            $output->writeln('<error> Check that you have the PHP bz2 extension installed and try again.');
            unlink($tarFile);
            return;
        }

        $artifacts = Filesystem::globr($extractionTarget, '*~~*');

        foreach($artifacts as $artifact) {
            $artifactName = basename($artifact);
            list($plugin, $file) = explode('~~', $artifactName);
            $pluginTargetDir = sprintf($targetDir, $plugin);
            Filesystem::mkdir($pluginTargetDir);
            Filesystem::copy($artifact, $pluginTargetDir . $file);
        }

        Filesystem::unlinkRecursive($extractionTarget, true);

        $this->writeSuccessMessage($output, array(
            'All processed plugin system test results were copied to <comment>' . $targetDir . '</comment>',
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

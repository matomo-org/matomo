<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\AssetManager;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestsRunUI extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-ui');
        $this->setDescription('Run screenshot tests');
        $this->addArgument('specs', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Run only a specific test spec. Separate multiple specs by comma, for instance core,integration', array());
        $this->addOption("persist-fixture-data", null, InputOption::VALUE_NONE, "Persist test data in a database and do not execute tear down.");
        $this->addOption('keep-symlinks', null, InputOption::VALUE_NONE, "Keep recursive directory symlinks so test pages can be viewed in a browser.");
        $this->addOption('print-logs', null, InputOption::VALUE_NONE, "Print webpage logs even if tests succeed.");
        $this->addOption('drop', null, InputOption::VALUE_NONE, "Drop the existing database and re-setup a persisted fixture.");
        $this->addOption('assume-artifacts', null, InputOption::VALUE_NONE, "Assume the diffviewer and processed screenshots will be stored on the builds artifacts server. For use with travis build.");
        $this->addOption('plugin', null, InputOption::VALUE_REQUIRED, "Execute all tests for a plugin.");
        $this->addOption('skip-delete-assets', null, InputOption::VALUE_NONE, "Skip deleting of merged assets (will speed up a test run, but not by a lot).");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $specs = $input->getArgument('specs');
        $persistFixtureData = $input->getOption("persist-fixture-data");
        $keepSymlinks = $input->getOption('keep-symlinks');
        $printLogs = $input->getOption('print-logs');
        $drop = $input->getOption('drop');
        $assumeArtifacts = $input->getOption('assume-artifacts');
        $plugin = $input->getOption('plugin');
        $skipDeleteAssets = $input->getOption('skip-delete-assets');

        if (!$skipDeleteAssets) {
            AssetManager::getInstance()->removeMergedAssets();
        }

        $options = array();
        if ($persistFixtureData) {
            $options[] = "--persist-fixture-data";
        }

        if ($keepSymlinks) {
            $options[] = "--keep-symlinks";
        }

        if ($printLogs) {
            $options[] = "--print-logs";
        }

        if ($drop) {
            $options[] = "--drop";
        }

        if ($assumeArtifacts) {
            $options[] = "--assume-artifacts";
        }

        if ($plugin) {
            $options[] = "--plugin=" . $plugin;
        }
        $options = implode(" ", $options);

        $specs = implode(" ", $specs);

        $cmd = "phantomjs '" . PIWIK_INCLUDE_PATH . "/tests/lib/screenshot-testing/run-tests.js' $options $specs";

        $output->writeln('Executing command: <info>' . $cmd . '</info>');
        $output->writeln('');

        passthru($cmd);
    }
}

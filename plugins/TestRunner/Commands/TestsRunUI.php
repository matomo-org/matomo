<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\AssetManager;
use Piwik\Config;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Tests\Framework\Fixture;

class TestsRunUI extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-ui');
        $this->setDescription('Run screenshot tests');
        $this->setHelp("Example Commands
        \nRun one spec:
        \n./console tests:run-ui UIIntegrationTest
        ");
        $this->addOptionalArgument('specs','Run only a specific test spec. Separate multiple specs by a space, for instance UIIntegrationTest ', [], true);
        $this->addNoValueOption("persist-fixture-data", null, "Persist test data in a database and do not execute tear down.");
        $this->addNoValueOption('keep-symlinks', null, "Keep recursive directory symlinks so test pages can be viewed in a browser.");
        $this->addNoValueOption('print-logs', null, "Print webpage logs even if tests succeed.");
        $this->addNoValueOption('drop', null, "Drop the existing database and re-setup a persisted fixture.");
        $this->addNoValueOption('assume-artifacts', null, "Assume the diffviewer and processed screenshots will be stored on the builds artifacts server. For use with CI build.");
        $this->addRequiredValueOption('plugin', null, "Execute all tests for a plugin.");
        $this->addNoValueOption('core', null, "Execute only tests for Piwik core & core plugins.");
        $this->addNoValueOption('skip-delete-assets', null, "Skip deleting of merged assets (will speed up a test run, but not by a lot).");
        $this->addNoValueOption('screenshot-repo', null, "For tests");
        $this->addNoValueOption('store-in-ui-tests-repo', null, "For tests");
        $this->addNoValueOption('debug', null, "Enables node inspector");
        $this->addRequiredValueOption('extra-options', null, "Extra options to pass to node.");
        $this->addNoValueOption('enable-logging', null, 'Enable logging to the configured log file during tests.');
        $this->addRequiredValueOption('timeout', null, 'Custom test timeout value.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $specs = $input->getArgument('specs');
        $persistFixtureData = $input->getOption('persist-fixture-data');
        $keepSymlinks = $input->getOption('keep-symlinks');
        $printLogs = $input->getOption('print-logs');
        $drop = $input->getOption('drop');
        $assumeArtifacts = $input->getOption('assume-artifacts');
        $plugin = $input->getOption('plugin');
        $skipDeleteAssets = $input->getOption('skip-delete-assets');
        $core = $input->getOption('core');
        $extraOptions = $input->getOption('extra-options');
        $storeInUiTestsRepo = $input->getOption('store-in-ui-tests-repo');
        $screenshotRepo = $input->getOption('screenshot-repo');
        $debug = $input->getOption('debug');
        $matomoDomain = $input->getOption('matomo-domain');
        $enableLogging = $input->getOption('enable-logging');
        $timeout = $input->getOption('timeout');

        if (!$skipDeleteAssets) {
            AssetManager::getInstance()->removeMergedAssets();
        }

        $this->writeJsConfig();

        $options = [];
        $additionalOptions = [];

        if ($matomoDomain) {
            $options[] = "--matomo-domain=$matomoDomain";
        }

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

        if ($core) {
            $options[] = "--core";
        }

        if ($storeInUiTestsRepo) {
            $options[] = "--store-in-ui-tests-repo";
        }

        if ($screenshotRepo) {
            $options[] = "--screenshot-repo";
        }

        if ($debug) {
            $additionalOptions[] = "--inspect";
        }

        if ($enableLogging) {
            $options[] = '--enable-logging';
        }

        if ($extraOptions) {
            $options[] = $extraOptions;
        }

        if ($timeout !== false && $timeout > 0) {
            $options[] = "--timeout=" . (int) $timeout;
        }

        $options = implode(" ", $options);
        $additionalOptions = implode(" ", $additionalOptions);

        $specs = implode(" ", $specs);

        $screenshotTestingDir = PIWIK_INCLUDE_PATH . "/tests/lib/screenshot-testing/";
        $cmd = "cd '$screenshotTestingDir' && NODE_PATH='$screenshotTestingDir/node_modules' node " . $additionalOptions . " run-tests.js $options $specs";

        $output->writeln('Executing command: <info>' . $cmd . '</info>');
        $output->writeln('');

        passthru($cmd, $returnCode);

        return $returnCode;
    }

    /**
     * We override the default values of tests/UI/config.dist.js with config
     * values from the local INI config.
     */
    private function writeJsConfig()
    {
        $localConfigFile = PIWIK_INCLUDE_PATH . '/tests/UI/config.js';
        $tag = 'File generated by the tests:run-ui command';

        // If the file wasn't generated by this command, we don't ovewrite it
        if (file_exists($localConfigFile)) {
            $fileContent = file_get_contents($localConfigFile);
            if (strpos($fileContent, $tag) === false) {
                return;
            }
        }

        $url = Fixture::getRootUrl();
        $host = Config::getInstance()->tests['http_host'];
        $uri = Config::getInstance()->tests['request_uri'];

        $js = <<<JS
/**
 * $tag
 */
exports.piwikUrl = "$url";
exports.phpServer = {
    HTTP_HOST: '$host',
    REQUEST_URI: '$uri',
    REMOTE_ADDR: '127.0.0.1'
};
JS;

        file_put_contents(PIWIK_INCLUDE_PATH . '/tests/UI/config.js', $js);
    }
}

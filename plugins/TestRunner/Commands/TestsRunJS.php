<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugin\ConsoleCommand;

class TestsRunJS extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-js');
        $this->setDescription('Run javascript tests');
        $this->addRequiredValueOption('matomo-url', null, 'Custom matomo url. Defaults to http://localhost');
        $this->addOptionalValueOption('plugin', null, 'The plugin to run tests for. If not supplied, all tests are run.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $matomoUrl = $input->getOption('matomo-url') ?? 'http://localhost';
        $plugin = $input->getOption('plugin');

        $screenshotTestingDir = PIWIK_INCLUDE_PATH . "/tests/lib/screenshot-testing";
        $javascriptTestingDir = PIWIK_INCLUDE_PATH . "/tests/javascript";

        $cmdNode = "cd '$javascriptTestingDir' && NODE_PATH='$screenshotTestingDir/node_modules' node testrunnerNode.js '$matomoUrl/tests/javascript/'";
        if (!empty($plugin)) {
            $cmdNode .= ' --plugin=' . escapeshellarg($plugin);
        }

        $output->writeln('Executing command: <info>' . $cmdNode . '</info>');
        $output->writeln('');

        passthru($cmdNode, $returnCodeNode);

        return $returnCodeNode;
    }
}

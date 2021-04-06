<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestsRunJS extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-js');
        $this->setDescription('Run javascript tests');
        $this->addOption('matomo-url', null, InputOption::VALUE_REQUIRED, 'Custom matomo url. Defaults to http://localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $matomoUrl = $input->getOption('matomo-url') ?? 'http://localhost';

        $screenshotTestingDir = PIWIK_INCLUDE_PATH . "/tests/lib/screenshot-testing/";
        $javascriptTestingDir = PIWIK_INCLUDE_PATH . "/tests/javascript";

        $cmd = "cd '$javascriptTestingDir' && NODE_PATH='$screenshotTestingDir/node_modules' node testrunner.js '$matomoUrl/tests/javascript/'";

        $output->writeln('Executing command: <info>' . $cmd . '</info>');
        $output->writeln('');

        passthru($cmd, $returnCode);

        return $returnCode;
    }
}

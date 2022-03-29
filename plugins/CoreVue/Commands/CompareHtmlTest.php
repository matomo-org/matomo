<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompareHtmlTest extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:vue-compare-html-test');
        $this->addOption('test', null, InputOption::VALUE_REQUIRED);
        $this->addOption('selector', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $test = $input->getOption('test');
        $selector = $input->getOption('selector');
        $branch = $this->getCurrentBranch();
        if ($branch == '4.x-dev') {
            throw new \Error('use another branch');
        }

        $this->runTests($output, '4.x-dev', $test, $selector);
        $this->runTests($output, $branch, $test, $selector);
    }

    protected function runTests(OutputInterface $output, $branch, $test, $selector)
    {
        $output->writeln("<comment>Checking out branch $branch...</comment>");
        $checkoutCommand = "git checkout $branch ; git submodule update --init";
        passthru($checkoutCommand, $returnCode);

        if ($returnCode) {
            throw new \Exception('Checkout command failed.');
        }

        $vueBuildCommand = "rm -rf plugins/*/vue/dist ; ./console vue:build --ignore-warn";
        passthru($vueBuildCommand, $returnCode);

        if ($returnCode) {
            throw new \Exception('vue:build command failed.');
        }

        $testRunCommand = "./console tests:run-ui --persist-fixture-data --keep-symlinks --extra-options='--outputHtml=\"$selector\" --outputHtmlBranch=\"$branch\"' $test";
        passthru($testRunCommand, $returnCode);

        if ($returnCode) {
            $output->writeln("<error>Test failed on $branch</error>");
        } else {
            $output->writeln("<info>Test succeeded on branch.</info>");
        }
    }

    private function getCurrentBranch()
    {
        return trim(`git symbolic-ref --short HEAD`);
    }
}

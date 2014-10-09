<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class CodeCoverage extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:coverage');
        $this->setDescription('Run all phpunit tests and generate a combined code coverage');
        $this->addOption('testsuite', null, InputOption::VALUE_REQUIRED, 'Run only a specific test suite, for instance UnitTests, IntegrationTests or SystemTests.');
        $this->addArgument('group', InputArgument::OPTIONAL, 'Run only a specific test group. Separate multiple groups by comma, for instance core,plugins', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phpCovPath = trim(shell_exec('which phpcov'));

        if (empty($phpCovPath)) {

            $output->writeln('phpcov not installed. please install pear.phpunit.de/phpcov.');
            return;
        }

        $command = $this->getApplication()->find('tests:run');
        $arguments = array(
            'command'   => 'tests:run',
            '--options' => sprintf('--coverage-php %s/tests/results/logs/%%suite%%%%group%%.cov', PIWIK_DOCUMENT_ROOT),
        );

        $suite = $input->getOption('testsuite');
        if (!empty($suite)) {
            $arguments['--testsuite'] = $suite;
        }

        $groups = $input->getArgument('group');
        if (!empty($groups)) {
            $arguments['group'] = $groups;
        } else {
            shell_exec(sprintf('rm %s/tests/results/logs/*.cov', PIWIK_DOCUMENT_ROOT));
        }

        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($input->isInteractive());
        $command->run($inputObject, $output);

        $command = 'phpcov';

        // force xdebug usage for coverage options
        if (!extension_loaded('xdebug')) {

            $output->writeln('<info>xdebug extension required for code coverage.</info>');

            $output->writeln('<info>searching for xdebug extension...</info>');

            $extensionDir = shell_exec('php-config --extension-dir');
            $xdebugFile   = trim($extensionDir) . DIRECTORY_SEPARATOR . 'xdebug.so';

            if (!file_exists($xdebugFile)) {

                $dialog = $this->getHelperSet()->get('dialog');

                $xdebugFile = $dialog->askAndValidate($output, 'xdebug not found. Please provide path to xdebug.so', function($xdebugFile) {
                    return file_exists($xdebugFile);
                });
            } else {

                $output->writeln('<info>xdebug extension found in extension path.</info>');
            }

            $output->writeln("<info>using $xdebugFile as xdebug extension.</info>");

            $command = sprintf('php -d zend_extension=%s %s', $xdebugFile, $phpCovPath);
        }

        shell_exec(sprintf('rm -rf %s/tests/results/coverage/*', PIWIK_DOCUMENT_ROOT));

        passthru(sprintf('cd %1$s && %2$s --merge --html tests/results/coverage/ --whitelist ./core/ --whitelist ./plugins/ --add-uncovered %1$s/tests/results/logs/', PIWIK_DOCUMENT_ROOT, $command));
    }

}

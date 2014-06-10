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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class RunTests extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run');
        $this->setDescription('Run Piwik PHPUnit tests one group after the other');
        $this->addArgument('group', InputArgument::OPTIONAL, 'Run only a specific test group. Separate multiple groups by comma, for instance core,integration', '');
        $this->addOption('options', 'o', InputOption::VALUE_OPTIONAL, 'All options will be forwarded to phpunit', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('options');
        $groups = $input->getArgument('group');

        $groups = explode(",", $groups);
        $groups = array_map('ucfirst', $groups);
        $groups = array_filter($groups, 'strlen');

        $command = '../../vendor/phpunit/phpunit/phpunit';

        // force xdebug usage for coverage options
        if (false !== strpos($options, '--coverage') && !extension_loaded('xdebug')) {

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

            $phpunitPath = trim(shell_exec('which phpunit'));

            $command = sprintf('php -d zend_extension=%s %s', $xdebugFile, $phpunitPath);
        }

        if(empty($groups)) {
            $groups = $this->getTestsGroups();
        }
        foreach($groups as $group) {
            $params = '--group ' . $group . ' ' . str_replace('%group%', $group, $options);
            $cmd = sprintf('cd %s/tests/PHPUnit && %s %s', PIWIK_DOCUMENT_ROOT, $command, $params);
            $output->writeln('Executing command: <info>' . $cmd . '</info>');
            passthru($cmd);
            $output->writeln("");
        }
    }

    private function getTestsGroups()
    {
        return array('Core', 'Plugins', 'Integration', 'UI');
    }

}

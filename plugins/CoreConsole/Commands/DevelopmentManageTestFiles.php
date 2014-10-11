<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevelopmentManageTestFiles extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('development:test-files');
        $this->setDescription("Manage test files.");

        $this->addArgument('operation', InputArgument::REQUIRED, 'The operation to apply. Supported operations include: '
            . '"copy"');
        $this->addOption('file', null, InputOption::VALUE_REQUIRED, "The file (or files) to apply the operation to.");

        // TODO: allow copying by regex pattern
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if ($operation == 'copy') {
            $this->copy($input, $output);
        } else {
            throw new \Exception("Invalid operation '$operation'.");
        }
    }

    private function copy($input, $output)
    {
        $file = $input->getOption('file');

        $prefix = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/System/processed/';
        $guesses = array(
            '/' . $file,
            $prefix . $file,
            $prefix . $file . '.xml'
        );

        foreach ($guesses as $guess) {
            if (is_file($guess)) {
                $file = $guess;
            }
        }

        copy($file, PIWIK_INCLUDE_PATH . '/tests/PHPUnit/System/expected/' . basename($file));
    }
}
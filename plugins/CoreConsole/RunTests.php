<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole;

use Piwik\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class RunTests extends Command
{
    protected function configure()
    {
        $this->setName('tests:run');
        $this->setDescription('Run Piwik PHPUnit tests');
        $this->addArgument('group', InputArgument::OPTIONAL, 'Run only a specific test group. Separate multiple groups by comma, for instance core,integration', '');
        $this->addOption('options', 'o', InputOption::VALUE_OPTIONAL, 'All options will be forwarded to phpunit', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('options');
        $group = $input->getArgument('group');

        if (!empty($group)) {
            $groups = explode(',', $group);
            $groups = array_map('ucfirst', $groups);
            $options = '--group ' . implode(',', $groups) . ' ' . $options;
        }

        $cmd = sprintf('cd %s/tests/PHPUnit && phpunit %s', PIWIK_DOCUMENT_ROOT, $options);

        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}
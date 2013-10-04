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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @package CoreConsole
 */
class RunTests extends Command
{
    protected function configure()
    {
        $this->setName('tests');
        $this->setDescription('Run Piwik PHPUnit tests');
        $this->addArgument('group', InputArgument::OPTIONAL, 'Optional test group', '');
        $this->addOption('options', 'o', InputOption::VALUE_OPTIONAL, 'All options will be forwarded to phpunit', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('options');
        $group = $input->getArgument('group');

        if (!empty($group)) {
            $options = '--group ' . ucfirst($group) . ' ' . $options;
        }

        $cmd = sprintf('cd %s/tests/PHPUnit && phpunit %s', PIWIK_DOCUMENT_ROOT, $options);

        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @package CoreConsole
 */
class GeneratePlugin extends Command
{
    protected function configure()
    {
        $this->setName('generate:plugin');
        $this->setDescription('Generates a new plugin including all needed files');
        $this->setDefinition(array(
            new InputArgument('name', InputArgument::REQUIRED, 'Plugin name ([a-Z0-9_-])'),
            new InputArgument('version', InputArgument::REQUIRED, 'Plugin version'),
            new InputArgument('description', InputArgument::REQUIRED, 'Plugin description, max 150 characters.')
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName  = $input->getArgument('name');
        $version     = $input->getArgument('version');
        $description = $input->getArgument('description');

        $output->writeln(sprintf('Dir listing for <info>%s</info>', $pluginName));
    }
}
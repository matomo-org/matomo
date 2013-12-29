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

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class PluginsWithTranslations extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('translations:plugins')
             ->setDescription('Shows all plugins that have own translation files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Following plugins contain their own translation files:");

        $pluginFiles = glob(sprintf('%s/plugins/*/lang/en.json', PIWIK_INCLUDE_PATH));
        $pluginFiles = array_map(function($elem){
            return str_replace(array(sprintf('%s/plugins/', PIWIK_INCLUDE_PATH), '/lang/en.json'), '', $elem);
        }, $pluginFiles);

        $output->writeln(join("\n", $pluginFiles));
    }
}
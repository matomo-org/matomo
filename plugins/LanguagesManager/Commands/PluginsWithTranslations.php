<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class PluginsWithTranslations extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:plugins')
             ->setDescription('Shows all plugins that have own translation files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Following plugins contain their own translation files:");

        $pluginFiles = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginFiles = array_merge($pluginsDir, glob(sprintf('%s*/lang/en.json', $pluginsDir)));
        }
        $pluginFiles = array_map(function($elem){
            $replace = Manager::getPluginsDirectories();
            $replace[] = '/lang/en.json';
            return str_replace($replace, '', $elem);
        }, $pluginFiles);

        $output->writeln(join("\n", $pluginFiles));
    }
}

<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link     https://matomo.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\Manager;
use Piwik\Plugins\LanguagesManager\API;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class Validate extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:validate')
            ->setDescription('Validates translation files')
            ->addOption('token', 't', InputOption::VALUE_OPTIONAL, 'Weblate API token')
            ->addOption('slug', 's', InputOption::VALUE_OPTIONAL, 'Weblate project slug')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Force to update all plugins (even non core). Can not be used with plugin option')
            ->addOption('plugin', 'P', InputOption::VALUE_OPTIONAL, 'optional name of plugin to update translations for');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);

        $start = microtime(true);

        $languages = API::getInstance()->getAvailableLanguageNames(true);

        $languageCodes = array();
        foreach ($languages as $languageInfo) {
            $languageCodes[] = $languageInfo['code'];
        }

        $plugin = $input->getOption('plugin');

        $pluginList = array($plugin);
        if (empty($plugin)) {
            $pluginList = self::getAllPlugins();
            array_unshift($pluginList, '');
        }

        file_put_contents(PIWIK_DOCUMENT_ROOT . '/filter.txt', '');

        foreach ($pluginList as $plugin) {

            $output->writeln("");

            // fetch base or specific plugin
            $this->fetchTranslations($input, $output, $plugin);

            $files = _glob(FetchTranslations::getDownloadPath() . DIRECTORY_SEPARATOR . '*.json');

            if (count($files) == 0) {
                $output->writeln("No translation updates available! Skipped.");
                continue;
            }

            foreach ($files as $filename) {

                $code = basename($filename, '.json');

                $command = $this->getApplication()->find('translations:set');
                $arguments = array(
                    'command' => 'translations:set',
                    '--code' => $code,
                    '--file' => $filename,
                    '--plugin' => $plugin,
                    '--validate' => PIWIK_DOCUMENT_ROOT . '/filter.txt'
                );
                $inputObject = new ArrayInput($arguments);
                $inputObject->setInteractive($input->isInteractive());
                $command->run($inputObject, $output);
            }

            $output->writeln('');
        }

        $output->writeln("Finished in " . round(microtime(true)-$start, 3) . "s");
    }

    /**
     * Returns all plugins having their own translations that are bundled in core
     * @return array
     */
    public static function getAllPlugins()
    {
        static $pluginsWithTranslations;

        if (!empty($pluginsWithTranslations)) {
            return $pluginsWithTranslations;
        }

        $pluginsWithTranslations = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginsWithTranslations = array_merge($pluginsWithTranslations, glob(sprintf('%s*/lang/en.json', $pluginsDir)));
        }
        $pluginsWithTranslations = array_map(function ($elem) {
            $replace = Manager::getPluginsDirectories();
            $replace[] = '/lang/en.json';
            return str_replace($replace, '', $elem);
        }, $pluginsWithTranslations);

        return $pluginsWithTranslations;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $plugin
     * @throws \Exception
     */
    protected function fetchTranslations(InputInterface $input, OutputInterface $output, $plugin)
    {

        $command = $this->getApplication()->find('translations:fetch');
        $arguments = array(
            'command'  => 'translations:fetch',
            '--token'  => $input->getOption('token'),
            '--slug'   => $input->getOption('slug'),
            '--plugin' => $plugin
        );

        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($input->isInteractive());
        $command->run($inputObject, $output);
    }
}

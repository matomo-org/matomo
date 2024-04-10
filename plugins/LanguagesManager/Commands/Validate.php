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

/**
 */
class Validate extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:validate')
            ->setDescription('Validates translation files')
            ->addOptionalValueOption('token', 't', 'Weblate API token')
            ->addOptionalValueOption('slug', 's', 'Weblate project slug')
            ->addNoValueOption('all', 'a', 'Force to update all plugins (even non core). Can not be used with plugin option')
            ->addOptionalValueOption('plugin', 'P', 'optional name of plugin to update translations for');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
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
            $this->fetchTranslations($plugin);

            $files = _glob(FetchTranslations::getDownloadPath() . DIRECTORY_SEPARATOR . '*.json');

            if (count($files) == 0) {
                $output->writeln("No translation updates available! Skipped.");
                continue;
            }

            foreach ($files as $filename) {
                $this->runCommand(
                    'translations:set',
                    [
                        '--code' => basename($filename, '.json'),
                        '--file' => $filename,
                        '--plugin' => $plugin,
                        '--validate' => PIWIK_DOCUMENT_ROOT . '/filter.txt'
                    ]
                );
            }

            $output->writeln('');
        }

        $output->writeln("Finished in " . round(microtime(true) - $start, 3) . "s");

        return self::SUCCESS;
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
     * @param string $plugin
     * @throws \Exception
     */
    protected function fetchTranslations($plugin)
    {
        $input = $this->getInput();
        $this->runCommand(
            'translations:fetch',
            [
                '--token'    => $input->getOption('token'),
                '--slug'     => $input->getOption('slug'),
                '--plugin'   => $plugin
            ]
        );
    }
}

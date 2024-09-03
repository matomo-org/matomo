<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Cache;
use Piwik\Plugin\Manager;
use Piwik\Plugins\LanguagesManager\API;

/**
 */
class Update extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:update')
            ->setDescription('Updates translation files')
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
        $forceAllPlugins = $input->getOption('all');

        if (!$input->isInteractive()) {
            $output->writeln("(!) Non interactive mode: New languages will be skipped");
        }

        $pluginList = array($plugin);
        if (empty($plugin)) {
            $pluginList = $forceAllPlugins ? self::getAllPlugins() : self::getPluginsInCore();
            array_unshift($pluginList, '');
        }

        foreach ($pluginList as $plugin) {
            $output->writeln("");

            // fetch base or specific plugin
            $this->fetchTranslations($plugin);

            $files = _glob(FetchTranslations::getDownloadPath() . DIRECTORY_SEPARATOR . '*.json');

            if (count($files) == 0) {
                $output->writeln("No translation updates available! Skipped.");
                continue;
            }

            $output->writeln("Starting to import new language files");

            $this->initProgressBar(count($files));
            $this->startProgressBar();

            foreach ($files as $filename) {
                $this->advanceProgressBar();

                $code = basename($filename, '.json');

                if (!in_array($code, $languageCodes)) {
                    if (!empty($plugin)) {
                        continue; # never create a new language for plugin only
                    }

                    $createNewFile = false;
                    if ($input->isInteractive()) {
                        $createNewFile = $this->askForConfirmation(
                            "\nLanguage $code does not exist. Should it be added? ",
                            false
                        );
                    }

                    if (!$createNewFile) {
                        continue; # do not create a new file for the language
                    }

                    @touch(PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $code . '.json');
                    API::unsetAllInstances(); // unset language manager instance, so valid names are refetched

                    $this->runCommand('translations:generate-intl-data', ['--language' => $code], !$output->isVeryVerbose());

                    API::unsetAllInstances(); // unset language manager instance, so valid names are refetched
                    Cache::flushAll();

                    $languageCodes[] = $code;
                }

                $this->runCommand(
                    'translations:set',
                    [
                        '--code' => $code,
                        '--file' => $filename,
                        '--plugin' => $plugin
                    ],
                    !$output->isVeryVerbose()
                );
            }

            $this->finishProgressBar();
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
     * Returns all plugins having their own translations that are bundled in core
     * @return array
     */
    public static function getPluginsInCore()
    {
        static $pluginsInCore;

        if (!empty($pluginsInCore)) {
            return $pluginsInCore;
        }

        $submodules = shell_exec('git submodule status');
        preg_match_all('/plugins\/([a-zA-z]+) /', $submodules, $matches);
        $submodulePlugins = $matches[1];

        // ignore complete new plugins as well
        $changes = shell_exec('git status');
        preg_match_all('/plugins\/([a-zA-z]+)\/\n/', $changes, $matches);
        $newPlugins = $matches[1];

        $pluginsNotInCore = array_merge($submodulePlugins, $newPlugins);
        $pluginsWithTranslations = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginsWithTranslations = array_merge($pluginsWithTranslations, glob(sprintf('%s*/lang/en.json', $pluginsDir)));
        }
        $pluginsWithTranslations = array_map(function ($elem) {
            $replace = Manager::getPluginsDirectories();
            $replace[] = '/lang/en.json';
            return str_replace($replace, '', $elem);
        }, $pluginsWithTranslations);

        $pluginsInCore = array_diff($pluginsWithTranslations, $pluginsNotInCore);

        return $pluginsInCore;
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

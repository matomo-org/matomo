<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Plugins\LanguagesManager\API as LanguagesManagerApi;
use Piwik\Translation\Transifex\API;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class FetchTranslations extends TranslationBase
{
    const DOWNLOAD_PATH = '/transifex';

    protected function configure()
    {
        $path = StaticContainer::get('path.tmp') . self::DOWNLOAD_PATH;

        $this->setName('translations:fetch')
             ->setDescription('Fetches translations files from Transifex to ' . $path)
             ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Transifex username')
             ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Transifex password')
             ->addOption('lastupdate', 'l', InputOption::VALUE_OPTIONAL, 'Last time update ran', time()-30*24*3600)
             ->addOption('plugin', 'r', InputOption::VALUE_OPTIONAL, 'Plugin to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $plugin = $input->getOption('plugin');
        $lastUpdate = $input->getOption('lastupdate');

        $resource = 'piwik-'. ($plugin ? 'plugin-'.strtolower($plugin) : 'base');

        $transifexApi = new API($username, $password);

        // remove all existing translation files in download path
        $files = glob($this->getDownloadPath() . DIRECTORY_SEPARATOR . '*.json');
        array_map('unlink', $files);

        if (!$transifexApi->resourceExists($resource)) {
            $output->writeln("Skipping resource $resource as it doesn't exist on Transifex");
            return;
        }

        $output->writeln("Fetching translations from Transifex for resource $resource");

        $availableLanguages = LanguagesManagerApi::getInstance()->getAvailableLanguageNames();

        $languageCodes = array();
        foreach ($availableLanguages as $languageInfo) {
            $languageCodes[] = $languageInfo['code'];
        }

        $languageCodes = array_filter($languageCodes, function($code) {
            return !in_array($code, array('en', 'dev'));
        });

        try {
            $languages = $transifexApi->getAvailableLanguageCodes();

            if (!empty($plugin)) {
                $languages = array_filter($languages, function ($language) {
                    return LanguagesManagerApi::getInstance()->isLanguageAvailable(str_replace('_', '-', strtolower($language)));
                });
            }
        } catch (AuthenticationFailedException $e) {
            $languages = $languageCodes;
        }

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');

        $progress->start($output, count($languages));

        $statistics = $transifexApi->getStatistics($resource);

        foreach ($languages as $language) {
            try {
                // if we have modification date given from statistics api compare it with given last update time to ignore not update resources
                if (LanguagesManagerApi::getInstance()->isLanguageAvailable(str_replace('_', '-', strtolower($language))) && isset($statistics->$language)) {
                    $lastupdated = strtotime($statistics->$language->last_update);
                    if ($lastUpdate > $lastupdated) {
                        $progress->advance();
                        continue;
                    }
                }

                $translations = $transifexApi->getTranslations($resource, $language, true);
                file_put_contents($this->getDownloadPath() . DIRECTORY_SEPARATOR . str_replace('_', '-', strtolower($language)) . '.json', $translations);
            } catch (\Exception $e) {
                $output->writeln("Error fetching language file $language: " . $e->getMessage());
            }
            $progress->advance();
        }

        $progress->finish();
    }

    public static function getDownloadPath()
    {
        $path = StaticContainer::get('path.tmp') . self::DOWNLOAD_PATH;

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }
}

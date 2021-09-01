<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Plugins\LanguagesManager\API as LanguagesManagerApi;
use Piwik\Translation\Weblate\API;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class FetchTranslations extends TranslationBase
{
    const DOWNLOAD_PATH = '/weblate';

    protected function configure()
    {
        $path = StaticContainer::get('path.tmp') . self::DOWNLOAD_PATH;

        $this->setName('translations:fetch')
             ->setDescription('Fetches translations files from Weblate to ' . $path)
             ->addOption('token', 't', InputOption::VALUE_OPTIONAL, 'Weblate API token')
             ->addOption('slug', 's', InputOption::VALUE_OPTIONAL, 'project slug on weblate', 'matomo')
             ->addOption('plugin', 'r', InputOption::VALUE_OPTIONAL, 'Plugin to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);

        $apiToken = $input->getOption('token');
        $plugin = $input->getOption('plugin');
        $slug = $input->getOption('slug');

        $resource = $plugin ? 'plugin-'.strtolower($plugin) : 'matomo-base';

        $weblateApi = new API($apiToken, $slug);

        // remove all existing translation files in download path
        $files = glob($this->getDownloadPath() . DIRECTORY_SEPARATOR . '*.json');
        array_map('unlink', $files);

        if (!$weblateApi->resourceExists($resource)) {
            $output->writeln("Skipping resource $resource as it doesn't exist on Weblate");
            return;
        }

        $output->writeln("Fetching translations from Weblate for resource $resource");

        try {
            $languages = $weblateApi->getAvailableLanguageCodes();

            if (!empty($plugin)) {
                $languages = array_filter($languages, function ($language) {
                    return LanguagesManagerApi::getInstance()->isLanguageAvailable(str_replace('_', '-', strtolower($language)), true);
                });
            }
        } catch (AuthenticationFailedException $e) {
            $availableLanguages = LanguagesManagerApi::getInstance()->getAvailableLanguageNames(true);

            $languageCodes = array();
            foreach ($availableLanguages as $languageInfo) {
                $codeParts = explode('-', $languageInfo['code']);

                if (!empty($codeParts[1])) {
                    $codeParts[1] = strtoupper($codeParts[1]);
                }

                $languageCodes[] = implode('_', $codeParts);
            }

            $languageCodes = array_filter($languageCodes, function($code) {
                return !in_array($code, array('en', 'dev'));
            });

            $languages = $languageCodes;
        }

        /** @var ProgressBar $progress */
        $progress = new ProgressBar($output, count($languages));

        $progress->start();

        foreach ($languages as $language) {
            try {
                $translations = $weblateApi->getTranslations($resource, $language, true);
                file_put_contents($this->getDownloadPath() . DIRECTORY_SEPARATOR . str_replace('_', '-', strtolower($language)) . '.json', $translations);
            } catch (\Exception $e) {
                $output->writeln("Error fetching language file $language: " . $e->getMessage());
            }
            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
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

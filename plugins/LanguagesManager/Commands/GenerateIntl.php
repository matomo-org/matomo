<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Aws\CloudFront\Exception\Exception;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Http;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Command to generate Intl-data files for Piwik
 *
 * This script uses the master data of unicode-cldr/cldr-localenames-full repository to fetch available translations
 */
class GenerateIntl extends TranslationBase
{
    protected function configure()
    {
        $this->setName('intl:generate')
             ->setDescription('Generates Intl-data for Piwik');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $piwikLanguages = \Piwik\Plugins\LanguagesManager\API::getInstance()->getAvailableLanguages();
        $languageCodes = array_keys(StaticContainer::get('Piwik\Intl\Data\Provider\LanguageDataProvider')->getLanguageList());
        $countryCodes = array_keys(StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider')->getCountryList());
        $countryCodes = array_map('strtoupper', $countryCodes);

        $languageDataUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-localenames-full/master/main/%s/languages.json';
        $languageWritePath = Filesystem::getPathToPiwikRoot() . '/core/Intl/Data/Resources/languages/%s.json';

        $countryDataUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-localenames-full/master/main/%s/territories.json';
        $countryWritePath = Filesystem::getPathToPiwikRoot() . '/core/Intl/Data/Resources/countries/%s.json';


        foreach ($piwikLanguages AS $langCode) {

            if ($langCode == 'dev') {
                continue;
            }

            $requestLangCode = $langCode;

            if (substr_count($langCode, '-') == 1) {
                $langCodeParts = explode('-', $langCode, 2);
                $requestLangCode = sprintf('%s-%s', $langCodeParts[0], strtoupper($langCodeParts[1]));
            }

            if ($langCode == 'zh-cn') {
                $requestLangCode = 'zh-Hans';
            }

            if ($langCode == 'zh-tw') {
                $requestLangCode = 'zh-Hant';
            }

            try {
                $languageData = Http::fetchRemoteFile(sprintf($languageDataUrl, $requestLangCode));
                $languageData = json_decode($languageData, true);
                $languageData = $languageData['main'][$requestLangCode]['localeDisplayNames']['languages'];

                $translations = (array) @json_decode(file_get_contents(sprintf($languageWritePath, $langCode)));

                if (empty($translations)) {
                    $translations = array_fill_keys($languageCodes, '');
                }

                foreach ($languageCodes AS $code) {
                    if (!empty($languageData[$code]) && $languageData[$code] != $code) {
                        $translations[$code] = $languageData[$code];
                    }
                }

                file_put_contents(sprintf($languageWritePath, $langCode), json_encode($translations, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
                $output->writeln('Saved language data for '.$langCode);
            } catch (Exception $e) {
                $output->writeln('Unable to import language data for '.$langCode);
            }

            try {
                $countryData = Http::fetchRemoteFile(sprintf($countryDataUrl, $requestLangCode));
                $countryData = json_decode($countryData, true);
                $countryData = $countryData['main'][$requestLangCode]['localeDisplayNames']['territories'];

                $translations = (array) @json_decode(file_get_contents(sprintf($countryWritePath, $langCode)));

                if (empty($translations)) {
                    $translations = array_fill_keys($countryCodes, '');
                }

                foreach ($countryCodes AS $code) {
                    if (!empty($countryData[$code]) && $countryData[$code] != $code) {
                        $translations[$code] = $countryData[$code];
                    }
                }

                file_put_contents(sprintf($countryWritePath, $langCode), json_encode($translations, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
                $output->writeln('Saved country data for '.$langCode);
            } catch (Exception $e) {
                $output->writeln('Unable to import country data for '.$langCode);
            }

        }
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Intl\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Command to generate Intl-data files for Piwik
 *
 * This script uses the master data of unicode-cldr/cldr-localenames-full repository to fetch available translations
 */
class GenerateIntl extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('translations:generate-intl-data')
            ->setDescription('Generates Intl-data for Piwik');
    }

    protected function transformLangCode($langCode)
    {
        if (substr_count($langCode, '-') == 1) {
            $langCodeParts = explode('-', $langCode, 2);
            return sprintf('%s-%s', $langCodeParts[0], strtoupper($langCodeParts[1]));
        }
        return $langCode;
    }

    protected function transform($str)
    {
        if (empty($str)) {
            return $str;
        }

        preg_match_all("~^(.)(.*)$~u", $str, $arr);
        return mb_strtoupper($arr[1][0], 'UTF-8').$arr[2][0];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $piwikLanguages = \Piwik\Plugins\LanguagesManager\API::getInstance()->getAvailableLanguages();

        $aliasesUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-core/master/supplemental/aliases.json';
        $aliasesData = Http::fetchRemoteFile($aliasesUrl);
        $aliasesData = json_decode($aliasesData, true);
        $aliasesData = $aliasesData['supplemental']['metadata']['alias']['languageAlias'];

        $writePath = Filesystem::getPathToPiwikRoot() . '/plugins/Intl/lang/%s.json';

        foreach ($piwikLanguages AS $langCode) {

            if ($langCode == 'dev') {
                continue;
            }

            $requestLangCode = $transformedLangCode = $this->transformLangCode($langCode);

            if (array_key_exists($requestLangCode, $aliasesData)) {
                $requestLangCode = $aliasesData[$requestLangCode]['_replacement'];
            }

            // fix some locales
            $localFixes = array(
                'pt' => 'pt-PT',
                'pt-br' => 'pt',
                'zh-cn' => 'zh-Hans',
                'zh-tw' => 'zh-Hant'
            );

            if (array_key_exists($langCode, $localFixes)) {
                $requestLangCode = $localFixes[$langCode];
            }

            setlocale(LC_ALL, $langCode);

            $translations = array();

            $this->fetchLanguageData($output, $transformedLangCode, $requestLangCode, $translations);
            $this->fetchTerritoryData($output, $transformedLangCode, $requestLangCode, $translations);
            $this->fetchCalendarData($output, $transformedLangCode, $requestLangCode, $translations);
            $this->fetchLayoutDirection($output, $transformedLangCode, $requestLangCode, $translations);
            $this->fetchUnitData($output, $transformedLangCode, $requestLangCode, $translations);
            $this->fetchNumberFormattingData($output, $transformedLangCode, $requestLangCode, $translations);

            ksort($translations['Intl']);

            file_put_contents(sprintf($writePath, $langCode), json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    protected function getEnglishLanguageName($code)
    {
        $languageDataUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-localenames-full/master/main/%s/languages.json';

        static $languageData = array();

        try {
            if (empty($languageData)) {
                $languageData = Http::fetchRemoteFile(sprintf($languageDataUrl, 'en'));
                $languageData = json_decode($languageData, true);
                $languageData = $languageData['main']['en']['localeDisplayNames']['languages'];
            }

            return (array_key_exists($code, $languageData) && $languageData[$code] != $code) ? $this->transform($languageData[$code]) : '';
        } catch (\Exception $e) {
        }

        return '';
    }

    protected function fetchLanguageData(OutputInterface $output, $langCode, $requestLangCode, &$translations)
    {
        $languageCodes = array_keys(StaticContainer::get('Piwik\Intl\Data\Provider\LanguageDataProvider')->getLanguageList());

        $languageDataUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-localenames-full/master/main/%s/languages.json';

        try {
            $languageData = Http::fetchRemoteFile(sprintf($languageDataUrl, $requestLangCode));
            $languageData = json_decode($languageData, true);
            $languageData = $languageData['main'][$requestLangCode]['localeDisplayNames']['languages'];

            if (empty($languageData)) {
                throw new \Exception();
            }

            foreach ($languageCodes AS $code) {
                if (!empty($languageData[$code]) && $languageData[$code] != $code) {
                    $translations['Intl']['Language_' . $code] = $this->transform($languageData[$code]);
                }
            }

            if (array_key_exists($langCode, $languageData) && $languageData[$langCode] != $langCode) {
                $translations['Intl']['OriginalLanguageName'] = $this->transform($languageData[$langCode]);
            } else if (array_key_exists($requestLangCode, $languageData) && $languageData[$requestLangCode] != $requestLangCode) {
                $translations['Intl']['OriginalLanguageName'] = $this->transform($languageData[$requestLangCode]);
            }
            $translations['Intl']['EnglishLanguageName'] = $this->getEnglishLanguageName($langCode) ? $this->getEnglishLanguageName($langCode) : $this->getEnglishLanguageName($requestLangCode);

            $output->writeln('Saved language data for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import language data for ' . $langCode);
        }
    }

    protected function fetchLayoutDirection(OutputInterface $output, $langCode, $requestLangCode, &$translations)
    {
        $layoutDirectionUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-misc-full/master/main/%s/layout.json';

        try {
            $layoutData = Http::fetchRemoteFile(sprintf($layoutDirectionUrl, $requestLangCode));
            $layoutData = json_decode($layoutData, true);
            $layoutData = $layoutData['main'][$requestLangCode]['layout']['orientation'];

            if (empty($layoutData)) {
                throw new \Exception();
            }

            $translations['Intl']['LayoutDirection'] = 'ltr';
            if ($layoutData['characterOrder'] == 'right-to-left') {
                $translations['Intl']['LayoutDirection'] = 'rtl';
            }

            $output->writeln('Saved language data for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import language data for ' . $langCode);
        }
    }

    protected function fetchTerritoryData(OutputInterface $output, $langCode, $requestLangCode, &$translations)
    {
        $territoryDataUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-localenames-full/master/main/%s/territories.json';

        $countryCodes = array_keys(StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider')->getCountryList());
        $countryCodes = array_map('strtoupper', $countryCodes);

        $continentMapping = array(
            "afr" => "002",
            "amc" => "013",
            "amn" => "003",
            "ams" => "005",
            "ant" => "AQ",
            "asi" => "142",
            "eur" => "150",
            "oce" => "009"
        );

        try {
            $territoryData = Http::fetchRemoteFile(sprintf($territoryDataUrl, $requestLangCode));
            $territoryData = json_decode($territoryData, true);
            $territoryData = $territoryData['main'][$requestLangCode]['localeDisplayNames']['territories'];

            foreach ($countryCodes AS $code) {
                if (!empty($territoryData[$code]) && $territoryData[$code] != $code) {
                    $translations['Intl']['Country_' . $code] = $this->transform($territoryData[$code]);
                }
            }

            foreach ($continentMapping as $shortCode => $code) {
                if (!empty($territoryData[$code]) && $territoryData[$code] != $code) {
                    $translations['Intl']['Continent_' . $shortCode] = $this->transform($territoryData[$code]);
                }
            }

            $output->writeln('Saved territory data for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import territory data for ' . $langCode);
        }
    }

    protected function fetchCalendarData(OutputInterface $output, $langCode, $requestLangCode, &$translations)
    {
        $calendarDataUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-dates-full/master/main/%s/ca-gregorian.json';

        try {
            $calendarData = Http::fetchRemoteFile(sprintf($calendarDataUrl, $requestLangCode));
            $calendarData = json_decode($calendarData, true);
            $calendarData = $calendarData['main'][$requestLangCode]['dates']['calendars']['gregorian'];

            for ($i = 1; $i <= 12; $i++) {
                $translations['Intl']['Month_Short_' . $i] = $this->transform($calendarData['months']['format']['abbreviated'][$i]);
                $translations['Intl']['Month_Long_' . $i] = $this->transform($calendarData['months']['format']['wide'][$i]);
                $translations['Intl']['Month_Short_StandAlone_' . $i] = $this->transform($calendarData['months']['stand-alone']['abbreviated'][$i]);
                $translations['Intl']['Month_Long_StandAlone_' . $i] = $this->transform($calendarData['months']['stand-alone']['wide'][$i]);
            }

            $days = array(
                1 => 'mon',
                2 => 'tue',
                3 => 'wed',
                4 => 'thu',
                5 => 'fri',
                6 => 'sat',
                7 => 'sun'
            );

            foreach ($days AS $nr => $day) {
                $translations['Intl']['Day_Min_' . $nr] = $this->transform($calendarData['days']['format']['short'][$day]);
                $translations['Intl']['Day_Short_' . $nr] = $this->transform($calendarData['days']['format']['abbreviated'][$day]);
                $translations['Intl']['Day_Long_' . $nr] = $this->transform($calendarData['days']['format']['wide'][$day]);
                $translations['Intl']['Day_Min_StandAlone_' . $nr] = $this->transform($calendarData['days']['stand-alone']['short'][$day]);
                $translations['Intl']['Day_Short_StandAlone_' . $nr] = $this->transform($calendarData['days']['stand-alone']['abbreviated'][$day]);
                $translations['Intl']['Day_Long_StandAlone_' . $nr] = $this->transform($calendarData['days']['stand-alone']['wide'][$day]);
            }

            $translations['Intl']['Time_AM'] = $calendarData['dayPeriods']['format']['wide']['am'];
            $translations['Intl']['Time_PM'] = $calendarData['dayPeriods']['format']['wide']['pm'];
            $translations['Intl']['Format_Time'] = $calendarData['timeFormats']['medium'];
            $translations['Intl']['Format_Date_Long'] = $calendarData['dateFormats']['full'];
            $translations['Intl']['Format_Date_Day_Month'] = $calendarData['dateTimeFormats']['availableFormats']['MMMEd'];
            $translations['Intl']['Format_Date_Short'] = $calendarData['dateFormats']['medium'];
            $translations['Intl']['Format_Month_Short'] = $calendarData['dateTimeFormats']['availableFormats']['yMMM'];
            $translations['Intl']['Format_Month_Long'] = $this->transformDateFormat($calendarData['dateTimeFormats']['availableFormats']['yMMM'], array('MMM' => 'MMMM', 'LLL' => 'LLLL'));
            if (isset($calendarData['dateTimeFormats']['availableFormats']['yMMMM'])) {
                $translations['Intl']['Format_Month_Long'] = $calendarData['dateTimeFormats']['availableFormats']['yMMMM'];
            }
            $translations['Intl']['Format_Year'] = $calendarData['dateTimeFormats']['availableFormats']['y'];

            $translations['Intl']['Format_DateTime_Long'] = $calendarData['dateFormats']['full'] . ' ' . $calendarData['timeFormats']['medium'];
            $translations['Intl']['Format_DateTime_Short'] = $calendarData['dateFormats']['medium'] . ' ' . $calendarData['timeFormats']['medium'];

            $translations['Intl']['Format_Interval_Long_D'] = $this->transformDateFormat($calendarData['dateTimeFormats']['intervalFormats']['yMMMd']['d'], array('MMMM' => 'MMM', 'LLLL' => 'LLL', 'MMM' => 'MMMM', 'LLL' => 'LLLL'));
            $translations['Intl']['Format_Interval_Long_M'] = $this->transformDateFormat($calendarData['dateTimeFormats']['intervalFormats']['yMMMd']['M'], array('MMMM' => 'MMM', 'LLLL' => 'LLL', 'MMM' => 'MMMM', 'LLL' => 'LLLL'));
            $translations['Intl']['Format_Interval_Long_Y'] = $this->transformDateFormat($calendarData['dateTimeFormats']['intervalFormats']['yMMMd']['y'], array('MMMM' => 'MMM', 'LLLL' => 'LLL', 'MMM' => 'MMMM', 'LLL' => 'LLLL'));

            if(isset($calendarData['dateTimeFormats']['intervalFormats']['yMMMMd'])) {
                $translations['Intl']['Format_Interval_Long_D'] = $calendarData['dateTimeFormats']['intervalFormats']['yMMMMd']['d'];
                $translations['Intl']['Format_Interval_Long_M'] = $calendarData['dateTimeFormats']['intervalFormats']['yMMMMd']['M'];
                $translations['Intl']['Format_Interval_Long_Y'] = $calendarData['dateTimeFormats']['intervalFormats']['yMMMMd']['y'];
            }

            $translations['Intl']['Format_Interval_Short_D'] = $calendarData['dateTimeFormats']['intervalFormats']['yMMMd']['d'];
            $translations['Intl']['Format_Interval_Short_M'] = $calendarData['dateTimeFormats']['intervalFormats']['yMMMd']['M'];
            $translations['Intl']['Format_Interval_Short_Y'] = $calendarData['dateTimeFormats']['intervalFormats']['yMMMd']['y'];

            $output->writeln('Saved calendar data for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import calendar data for ' . $langCode);
        }

        $dateFieldsUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-dates-full/master/main/%s/dateFields.json';

        try {
            $dateFieldData = Http::fetchRemoteFile(sprintf($dateFieldsUrl, $requestLangCode));
            $dateFieldData = json_decode($dateFieldData, true);
            $dateFieldData = $dateFieldData['main'][$requestLangCode]['dates']['fields'];

            $translations['Intl']['PeriodWeek'] = $dateFieldData['week']['displayName'];
            $translations['Intl']['PeriodYear'] = $dateFieldData['year']['displayName'];
            $translations['Intl']['PeriodDay'] = $dateFieldData['day']['displayName'];
            $translations['Intl']['PeriodMonth'] = $dateFieldData['month']['displayName'];
            $translations['Intl']['Year_Short'] = $dateFieldData['year-narrow']['displayName'];
            $translations['Intl']['Today'] = $this->transform($dateFieldData['day']['relative-type-0']);
            $translations['Intl']['Yesterday'] = $this->transform($dateFieldData['day']['relative-type--1']);

            $output->writeln('Saved date fields for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import date fields for ' . $langCode);
        }
    }

    protected function transformDateFormat($dateFormat, $changes=array())
    {
        if(!empty($changes)) {
            $dateFormat = str_replace(array_keys($changes), array_values($changes), $dateFormat);
        }

        return $dateFormat;
    }

    protected function fetchNumberFormattingData(OutputInterface $output, $langCode, $requestLangCode, &$translations)
    {
        $unitsUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-numbers-full/master/main/%s/numbers.json';

        try {
            $unitsData = Http::fetchRemoteFile(sprintf($unitsUrl, $requestLangCode));
            $unitsData = json_decode($unitsData, true);
            $unitsData = $unitsData['main'][$requestLangCode]['numbers'];

            $numberingSystem = $unitsData['defaultNumberingSystem'];

            $translations['Intl']['NumberSymbolDecimal']  = $unitsData['symbols-numberSystem-' . $numberingSystem]['decimal'];
            $translations['Intl']['NumberSymbolGroup']    = $unitsData['symbols-numberSystem-' . $numberingSystem]['group'];
            $translations['Intl']['NumberSymbolPercent']  = $unitsData['symbols-numberSystem-' . $numberingSystem]['percentSign'];
            $translations['Intl']['NumberSymbolPlus']     = $unitsData['symbols-numberSystem-' . $numberingSystem]['plusSign'];
            $translations['Intl']['NumberSymbolMinus']    = $unitsData['symbols-numberSystem-' . $numberingSystem]['minusSign'];
            $translations['Intl']['NumberFormatNumber']   = $unitsData['decimalFormats-numberSystem-' . $numberingSystem]['standard'];
            $translations['Intl']['NumberFormatCurrency']   = $unitsData['currencyFormats-numberSystem-' . $numberingSystem]['standard'];
            $translations['Intl']['NumberFormatPercent']  = $unitsData['percentFormats-numberSystem-' . $numberingSystem]['standard'];

            $output->writeln('Saved number formatting data for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import number formatting data for ' . $langCode);
        }
    }

    protected function fetchUnitData(OutputInterface $output, $langCode, $requestLangCode, &$translations)
    {
        $unitsUrl = 'https://raw.githubusercontent.com/unicode-cldr/cldr-units-full/master/main/%s/units.json';

        try {
            $unitsData = Http::fetchRemoteFile(sprintf($unitsUrl, $requestLangCode));
            $unitsData = json_decode($unitsData, true);
            $unitsData = $unitsData['main'][$requestLangCode]['units'];

            $translations['Intl']['NSeconds']       = $this->replacePlaceHolder($unitsData['long']['duration-second']['unitPattern-count-other']);
            $translations['Intl']['NSecondsShort']  = $this->replacePlaceHolder($unitsData['narrow']['duration-second']['unitPattern-count-other']);
            $translations['Intl']['Seconds']        = $unitsData['long']['duration-second']['displayName'];

            $translations['Intl']['NMinutes']       = $this->replacePlaceHolder($unitsData['long']['duration-minute']['unitPattern-count-other']);

            if (isset($unitsData['long']['duration-minute']['unitPattern-count-one'])) {
                $translations['Intl']['OneMinute'] = $this->replacePlaceHolder($unitsData['long']['duration-minute']['unitPattern-count-one'], '1');
            } else {
                $translations['Intl']['OneMinute'] = $this->replacePlaceHolder($unitsData['long']['duration-minute']['unitPattern-count-other'], '1');
            }

            if (isset($unitsData['short']['duration-minute']['unitPattern-count-one'])) {
                $translations['Intl']['OneMinuteShort'] = $this->replacePlaceHolder($unitsData['short']['duration-minute']['unitPattern-count-one'], '1');
            } else {
                $translations['Intl']['OneMinuteShort'] = $this->replacePlaceHolder($unitsData['short']['duration-minute']['unitPattern-count-other'], '1');
            }

            $translations['Intl']['NMinutesShort'] = $this->replacePlaceHolder($unitsData['short']['duration-minute']['unitPattern-count-other']);

            $translations['Intl']['Minutes']        = $unitsData['long']['duration-minute']['displayName'];

            $translations['Intl']['Hours']          = $unitsData['long']['duration-hour']['displayName'];
            $translations['Intl']['NHoursShort']    = $this->replacePlaceHolder($unitsData['narrow']['duration-hour']['unitPattern-count-other']);

            $translations['Intl']['NDays']          = $this->replacePlaceHolder($unitsData['long']['duration-day']['unitPattern-count-other']);

            if (isset($unitsData['short']['duration-day']['unitPattern-count-one'])) {
                $translations['Intl']['OneDay']         = $this->replacePlaceHolder($unitsData['long']['duration-day']['unitPattern-count-one'], '1');
            } else {
                $translations['Intl']['OneDay']         = $this->replacePlaceHolder($unitsData['long']['duration-day']['unitPattern-count-other'], '1');
            }

            $translations['Intl']['PeriodWeeks']    = $unitsData['long']['duration-week']['displayName'];
            $translations['Intl']['PeriodYears']    = $unitsData['long']['duration-year']['displayName'];
            $translations['Intl']['PeriodDays']     = $unitsData['long']['duration-day']['displayName'];
            $translations['Intl']['PeriodMonths']   = $unitsData['long']['duration-month']['displayName'];


            $output->writeln('Saved unit data for ' . $langCode);
        } catch (\Exception $e) {
            $output->writeln('Unable to import unit data for ' . $langCode);
        }
    }

    protected function replacePlaceHolder($string, $replacement = '%s')
    {
        return str_replace('{0}', $replacement, $string);
    }

}

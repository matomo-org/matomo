<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugins\LanguagesManager\API;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByBaseTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByParameterCount;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EmptyTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EncodedEntities;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\UnnecassaryWhitespaces;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\CoreTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\NoScripts;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Writer;

class SetTranslations extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:set')
             ->setDescription('Sets new translations for a given language')
             ->addRequiredValueOption('code', 'c', 'code of the language to set translations for')
             ->addRequiredValueOption('file', 'f', 'json file to load new translations from')
             ->addOptionalValueOption('plugin', 'pl', 'optional name of plugin to set translations for')
             ->addOptionalValueOption('validate', '', 'when set, the file will not be written, but validated. The given value will be used as filename to write filter results to.');
    }

    protected function doExecute(): int
    {
        $input        = $this->getInput();
        $output       = $this->getOutput();
        $languageCode = $input->getOption('code');
        $filename     = $input->getOption('file');

        $languageCodes = (new API())->getAvailableLanguages(true);

        if (empty($languageCode) || !in_array($languageCode, $languageCodes)) {
            $languageCode = $this->askAndValidate(
                'Please provide a valid language code: ',
                function ($code) use ($languageCodes) {
                    if (!in_array($code, array_values($languageCodes))) {
                        throw new \InvalidArgumentException(sprintf('Language code "%s" is invalid.', $code));
                    }

                    return $code;
                }
            );
        }

        if (empty($filename) || !file_exists($filename)) {
            $filename = $this->askAndValidate('Please provide a file to load translations from: ', function ($file) {
                if (!file_exists($file)) {
                    throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $file));
                }

                return $file;
            });
        }

        $output->writeln("Starting to import data from '$filename' to language '$languageCode'");

        $plugin = $input->getOption('plugin');
        $translationWriter = new Writer($languageCode, $plugin);

        $baseTranslations = $translationWriter->getTranslations("en");

        $translationWriter->addValidator(new NoScripts());
        if (empty($plugin)) {
            $translationWriter->addValidator(new CoreTranslations($baseTranslations));
        }

        $translationWriter->addFilter(new ByBaseTranslations($baseTranslations));
        $translationWriter->addFilter(new EmptyTranslations());
        $translationWriter->addFilter(new ByParameterCount($baseTranslations));
        $translationWriter->addFilter(new UnnecassaryWhitespaces($baseTranslations));
        $translationWriter->addFilter(new EncodedEntities($baseTranslations));

        $translationData = file_get_contents($filename);
        $translations = json_decode($translationData, true);

        $translationWriter->setTranslations($translations);

        if (!$translationWriter->isValid()) {
            $output->writeln("Failed setting translations:" . $translationWriter->getValidationMessage());
            return self::FAILURE;
        }

        if (!$translationWriter->hasTranslations()) {
            $output->writeln("No translations available");
            return self::SUCCESS;
        }

        if ($input->getOption('validate')) {
            $translationWriter->applyFilters();
            $filteredData = $translationWriter->getFilteredData();
            unset($filteredData[EmptyTranslations::class]);

            if (!empty($filteredData)) {
                $content = "Filtered File: " . ($plugin ?? 'Base') . " / " . $languageCode . "\n";
                foreach ($filteredData as $filter => $data) {
                    $content .= "- Filtered by: $filter\n";
                    $content .= json_encode($data, JSON_PRETTY_PRINT);
                    $content .= "\n";
                }
                $content .= "\n\n";
                file_put_contents($input->getOption('validate'), $content, FILE_APPEND);
            }
        } else {
            $translationWriter->save();
        }

        $output->writeln("Finished.");

        return self::SUCCESS;
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\LanguagesManager\API;
use Piwik\Translate\Filter\ByBaseTranslations;
use Piwik\Translate\Filter\ByParameterCount;
use Piwik\Translate\Filter\EmptyTranslations;
use Piwik\Translate\Filter\EncodedEntities;
use Piwik\Translate\Filter\UnnecassaryWhitespaces;
use Piwik\Translate\Validate\CoreTranslations;
use Piwik\Translate\Validate\NoScripts;
use Piwik\Translate\Writer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class SetTranslations extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('translations:set')
             ->setDescription('Sets new translations for a given language')
             ->addOption('code', 'c', InputOption::VALUE_REQUIRED, 'code of the language to set translations for')
             ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'json file to load new translations from')
             ->addOption('plugin', 'pl', InputOption::VALUE_OPTIONAL, 'optional name of plugin to set translations for');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $languageCode = $input->getOption('code');
        $filename     = $input->getOption('file');

        $languageCodes = API::getInstance()->getAvailableLanguages();

        if (empty($languageCode) || !in_array($languageCode, $languageCodes)) {
            $languageCode = $dialog->askAndValidate($output, 'Please provide a valid language code: ', function ($code) use ($languageCodes) {
                if (!in_array($code, array_values($languageCodes))) {
                    throw new \InvalidArgumentException(sprintf('Language code "%s" is invalid.', $code));
                }

                return $code;
            });
        }

        if (empty($filename) || !file_exists($filename)) {
            $filename = $dialog->askAndValidate($output, 'Please provide a file to load translations from: ', function ($file) {
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
        $translationWriter->addFilter(new EncodedEntities());

        $translationData = file_get_contents($filename);
        $translations = json_decode($translationData, true);

        $translationWriter->setTranslations($translations);

        if (!$translationWriter->isValid()) {
            $output->writeln("Failed setting translations:" . $translationWriter->getValidationMessage());
            return;
        }

        if (!$translationWriter->hasTranslations()) {
            $output->writeln("No translations available");
            return;
        }

        $translationWriter->save();

        $output->writeln("Finished.");
    }
}

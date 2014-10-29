<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugins\LanguagesManager\API;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class LanguageCodes extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:languagecodes')
             ->setDescription('Shows available language codes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = API::getInstance()->getAvailableLanguageNames();

        $languageCodes = array();
        foreach ($languages as $languageInfo) {
            $languageCodes[] = $languageInfo['code'];
        }

        sort($languageCodes);

        $output->writeln("Currently available languages:");
        $output->writeln(implode("\n", $languageCodes));
    }
}

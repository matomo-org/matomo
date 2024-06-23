<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugins\LanguagesManager\API;

/**
 */
class LanguageCodes extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:languagecodes')
             ->addNoValueOption('all', 'a', 'Displays all languages (ignores language configuration)')
             ->setDescription('Shows available language codes');
    }

    protected function doExecute(): int
    {
        $languages = API::getInstance()->getAvailableLanguageNames($this->getInput()->getOption('all'));

        $languageCodes = [];
        foreach ($languages as $languageInfo) {
            $languageCodes[] = $languageInfo['code'];
        }

        sort($languageCodes);

        $this->getOutput()->writeln("Currently available languages:");
        $this->getOutput()->writeln(implode("\n", $languageCodes));

        return self::SUCCESS;
    }
}

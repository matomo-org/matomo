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
class LanguageNames extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:languagenames')
             ->addNoValueOption('all', 'a', 'Displays all languages (ignores language configuration)')
             ->setDescription('Shows available language names');
    }

    protected function doExecute(): int
    {
        $languages = API::getInstance()->getAvailableLanguageNames($this->getInput()->getOption('all'));

        $languageNames = [];
        foreach ($languages as $languageInfo) {
            $languageNames[] = $languageInfo['english_name'];
        }

        sort($languageNames);

        $this->getOutput()->writeln("Currently available languages:");
        $this->getOutput()->writeln(implode("\n", $languageNames));

        return self::SUCCESS;
    }
}

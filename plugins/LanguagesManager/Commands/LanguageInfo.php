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
class LanguageInfo extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:languageinfo')
             ->addNoValueOption('all', 'a', 'Displays all languages (ignores language configuration)')
             ->setDescription('Shows available languages info');
    }

    protected function doExecute(): int
    {
        $languages = API::getInstance()->getAvailableLanguagesInfo(true, $this->getInput()->getOption('all'));

        foreach ($languages as $languageInfo) {
            $this->getOutput()->writeln($languageInfo['code'] . '|' . $languageInfo['english_name'] . '|' . $languageInfo['percentage_complete']);
        }

        return self::SUCCESS;
    }
}

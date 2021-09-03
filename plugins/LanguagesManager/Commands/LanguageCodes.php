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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class LanguageCodes extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:languagecodes')
             ->addOption('all', 'a', InputOption::VALUE_NONE, 'Displays all languages (ignores language configuration)')
             ->setDescription('Shows available language codes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = API::getInstance()->getAvailableLanguageNames($input->getOption('all'));

        $languageCodes = array();
        foreach ($languages as $languageInfo) {
            $languageCodes[] = $languageInfo['code'];
        }

        sort($languageCodes);

        $output->writeln("Currently available languages:");
        $output->writeln(implode("\n", $languageCodes));
    }
}

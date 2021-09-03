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
class LanguageInfo extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:languageinfo')
             ->addOption('all', 'a', InputOption::VALUE_NONE, 'Displays all languages (ignores language configuration)')
             ->setDescription('Shows available languages info');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = API::getInstance()->getAvailableLanguagesInfo(true, $input->getOption('all'));

        foreach ($languages as $languageInfo) {
            $output->writeln($languageInfo['code'].'|' . $languageInfo['english_name'] . '|' . $languageInfo['percentage_complete']);
        }
    }
}

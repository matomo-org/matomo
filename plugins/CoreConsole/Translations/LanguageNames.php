<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole\Translations;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\LanguagesManager\API;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class LanguageNames extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('translations:languagenames')
             ->setDescription('Shows available language names');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = API::getInstance()->getAvailableLanguageNames();

        $languageNames = array();
        foreach ($languages AS $languageInfo) {
            $languageNames[] = $languageInfo['english_name'];
        }

        sort($languageNames);

        $output->writeln("Currently available languages:");
        $output->writeln(implode("\n", $languageNames));
    }
}
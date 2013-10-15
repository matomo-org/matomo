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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class PluginsWithTranslations extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('translations:plugins')
             ->setDescription('Shows all plugins that have own translation files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Following plugins contain their own translation files:");
        $command = sprintf('ls -d1 %s/plugins/*/lang | egrep -o "([a-zA-Z]+)/lang" | '.
                           'awk \'{print substr($1, 0, length($1)-5)}\' | uniq | sort',
                           PIWIK_DOCUMENT_ROOT);
        passthru($command);
    }
}
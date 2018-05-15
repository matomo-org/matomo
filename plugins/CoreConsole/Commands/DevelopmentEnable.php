<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Config;
use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class DevelopmentEnable extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('development:enable');
        $this->setAliases(array('development:disable'));
        $this->setDescription('Enable or disable development mode. See config/global.ini.php in section [Development] for more information');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandName = $input->getFirstArgument();
        $enable      = (false !== strpos($commandName, 'enable'));

        $config      = Config::getInstance();
        $development = $config->Development;

        if ($enable) {
            $development['enabled'] = 1;
            $development['disable_merged_assets'] = 1;
            $message = 'Development mode enabled';
        } else {
            $development['enabled'] = 0;
            $development['disable_merged_assets'] = 0;
            $message = 'Development mode disabled';
        }

        $config->Development = $development;
        $config->forceSave();

        Filesystem::deleteAllCacheOnUpdate();

        $this->writeSuccessMessage($output, array($message));
    }

}

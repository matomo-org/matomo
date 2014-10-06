<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GenerateMenu extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:menu')
            ->setDescription('Adds a plugin menu class to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin which does not have a menu defined yet');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName, $output);

        $exampleFolder  = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
        $replace        = array('ExamplePlugin' => $pluginName);
        $whitelistFiles = array('/Menu.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
             sprintf('Menu.php for %s generated.', $pluginName),
             'You can now start defining your plugin menu',
             'Enjoy!'
        ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Menu.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have a menu defined';

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }

}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\Manager;

/**
 */
class GenerateMenu extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:menu')
            ->setDescription('Adds a plugin menu class to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have a menu defined yet');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin' => $pluginName);
        $whitelistFiles = array('/Menu.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('Menu.php for %s generated.', $pluginName),
             'You can now start defining your plugin menu',
             'Enjoy!'
        ));

        return self::SUCCESS;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getPluginName()
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Menu.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have a menu defined';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}

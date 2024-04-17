<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\Manager;

/**
 */
class GenerateController extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:controller')
            ->setDescription('Adds a Controller to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have a Controller yet');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin' => $pluginName);
        $whitelistFiles = array('/Controller.php', '/templates', '/templates/index.twig');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('Controller for %s generated.', $pluginName),
             'You can now start adding Controller actions',
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
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Controller.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have a Controller';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}

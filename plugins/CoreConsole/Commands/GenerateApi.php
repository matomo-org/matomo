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
class GenerateApi extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:api')
            ->setDescription('Adds an API to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have an API yet');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin' => $pluginName);
        $whitelistFiles = array('/API.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('API.php for %s generated.', $pluginName),
             'You can now start adding API methods',
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
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('API.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have an API';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}

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
class GenerateArchiver extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:archiver')
            ->setDescription('Adds an Archiver to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have an Archiver yet');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin' => $pluginName, 'EXAMPLEPLUGIN' => strtoupper($pluginName));
        $whitelistFiles = array('/Archiver.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('Archiver.php for %s generated.', $pluginName),
             'You can now start implementing Archiver methods',
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
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Archiver.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have an Archiver';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}

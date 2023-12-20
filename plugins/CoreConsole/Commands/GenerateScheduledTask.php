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
class GenerateScheduledTask extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:scheduledtask')
            ->setDescription('Adds a tasks class to an existing plugin which allows you to specify scheduled tasks')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have any tasks defined yet');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin' => $pluginName);
        $whitelistFiles = array('/Tasks.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('Tasks.php for %s generated.', $pluginName),
             'You can now start specifying your scheduled tasks',
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
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Tasks.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have any tasks defined';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}

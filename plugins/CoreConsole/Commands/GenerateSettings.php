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
class GenerateSettings extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:settings')
            ->setDescription('Adds a SystemSetting, UserSetting or MeasurableSetting class to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have settings yet')
            ->addRequiredValueOption('settingstype', null, 'The type of settings you want to create. Should be one of these values: ' . implode(', ', $this->getSettingTypes()));
    }

    protected function doExecute(): int
    {
        $settingsType = $this->getSettingsType();
        $settingsFilename = $settingsType . '.php';

        $pluginName = $this->getPluginName($settingsType, $settingsFilename);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $exampleFolder  = Manager::getPluginDirectory('ExampleSettingsPlugin');
        $replace        = array('ExampleSettingsPlugin' => $pluginName);
        $whitelistFiles = array('/' . $settingsFilename);

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
             sprintf('%s for %s generated.', $settingsFilename, $pluginName),
             'You can now start defining your ' . $settingsType,
             'Enjoy!'
        ));

        return self::SUCCESS;
    }

    private function getSettingTypes()
    {
        return array('system', 'user', 'measurable');
    }

    private function getSettingsType()
    {
        $input = $this->getInput();
        $availableTypes = $this->getSettingTypes();

        $validate = function ($type) use ($availableTypes) {
            if (empty($type) || !in_array($type, $availableTypes)) {
                throw new \InvalidArgumentException('Please enter a valid settings type (' . implode(', ', $availableTypes) .  '). ');
            }

            return $type;
        };

        $settingsType = $input->getOption('settingstype');

        if (empty($settingsType)) {
            $settingsType = $this->askAndValidate(
                'Please choose the type of settings you want to create (' . implode(', ', $availableTypes) . '): ',
                $validate,
                null,
                $availableTypes
            );
        } else {
            $validate($settingsType);
        }

        return ucfirst($settingsType) . 'Settings';
    }

    /**
     * @param string $settingsType
     * @return string
     * @throws \RuntimeException
     */
    protected function getPluginName($settingsType, $settingsFile)
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile($settingsFile);
        $invalidName = 'You have to enter the name of an existing plugin which does not already have ' . $settingsType;

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}

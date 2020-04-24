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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GenerateSettings extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:settings')
            ->setDescription('Adds a SystemSetting, UserSetting or MeasurableSetting class to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin which does not have settings yet')
            ->addOption('settingstype', null, InputOption::VALUE_REQUIRED, 'The type of settings you want to create. Should be one of these values: ' . implode(', ', $this->getSettingTypes()));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settingsType = $this->getSettingsType($input, $output);
        $settingsFilename = $settingsType . '.php';

        $pluginName = $this->getPluginName($input, $output, $settingsType, $settingsFilename);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName, $output);

        $exampleFolder  = Manager::getPluginDirectory('ExampleSettingsPlugin');
        $replace        = array('ExampleSettingsPlugin' => $pluginName);
        $whitelistFiles = array('/' . $settingsFilename);

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
             sprintf('%s for %s generated.', $settingsFilename, $pluginName),
             'You can now start defining your ' . $settingsType,
             'Enjoy!'
        ));
    }

    private function getSettingTypes()
    {
        return array('system', 'user', 'measurable');
    }

    private function getSettingsType(InputInterface $input, OutputInterface $output)
    {
        $availableTypes = $this->getSettingTypes();

        $validate = function ($type) use ($availableTypes) {
            if (empty($type) || !in_array($type, $availableTypes)) {
                throw new \InvalidArgumentException('Please enter a valid settings type (' . implode(', ', $availableTypes) .  '). ');
            }

            return $type;
        };

        $settingsType = $input->getOption('settingstype');

        if (empty($settingsType)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $settingsType = $dialog->askAndValidate($output, 'Please choose the type of settings you want to create (' . implode(', ', $availableTypes) .  '): ', $validate, false, null, $availableTypes);
        } else {
            $validate($settingsType);
        }

        return ucfirst($settingsType) . 'Settings';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $settingsType
     * @return array
     * @throws \RuntimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output, $settingsType, $settingsFile)
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile($settingsFile);
        $invalidName = 'You have to enter the name of an existing plugin which does not already have ' . $settingsType;

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }

}

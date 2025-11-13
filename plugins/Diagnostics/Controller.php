<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics;

use Piwik\Db\Schema;
use Piwik\Piwik;
use Piwik\Plugin\SettingsProvider;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
        parent::__construct();
    }

    public function configfile()
    {
        Piwik::checkUserHasSuperUserAccess();

        $settings = new SettingsProvider(\Piwik\Plugin\Manager::getInstance());
        $allSettings = $settings->getAllSystemSettings();

        $configValues = $this->configReader->getConfigValuesFromFiles();
        $configValues = $this->configReader->addConfigValuesFromSystemSettings($configValues, $allSettings);
        $configValues = $this->injectMinimumSupportedVersion($configValues);
        $configValues = $this->sortConfigValues($configValues);
        $configValues = array_filter($configValues);

        return $this->renderTemplate('configfile', array(
            'allConfigValues' => $configValues,
        ));
    }

    /**
     * Returns the minimum supported version of the database schema.
     * @return string
     */
    protected function getDbMinimumSupportedVersion()
    {
        return Schema::getInstance()->getMinimumSupportedVersion();
    }

    /**
     * Since we removed the minimum supported version from the config file, we need to add it here.
     * @param array $configValues
     * @return array
     */
    protected function injectMinimumSupportedVersion($configValues)
    {
        $minimumSupportedVersion = $this->getDbMinimumSupportedVersion();
        $valuesArray = [
            'value' => $minimumSupportedVersion,
            'description' => 'MySQL minimum required version note: timezone support added in 4.1.3',
            'isCustomValue' => false,
            'defaultValue' => $minimumSupportedVersion,
        ];
        $configValues['General']['minimum_mysql_version'] = $valuesArray;
        return $configValues;
    }

    private function sortConfigValues($configValues)
    {
        // we sort by sections alphabetically
        uksort($configValues, function ($section1, $section2) {
            return strcasecmp($section1, $section2);
        });

        foreach ($configValues as $category => &$settings) {
            // we sort keys alphabetically but list the ones that are changed first
            uksort($settings, function ($setting1, $setting2) use ($settings) {
                if ($settings[$setting1]['isCustomValue'] && !$settings[$setting2]['isCustomValue']) {
                    return -1;
                } elseif (!$settings[$setting1]['isCustomValue'] && $settings[$setting2]['isCustomValue']) {
                    return 1;
                }
                return strcasecmp($setting1, $setting2);
            });
        }

        return $configValues;
    }
}

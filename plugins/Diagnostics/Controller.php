<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Settings;

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

        $allSettings = Settings\Manager::getAllPluginSettings();

        $configValues = $this->configReader->getConfigValuesFromFiles();
        $configValues = $this->configReader->addConfigValuesFromPluginSettings($configValues, $allSettings);
        $configValues = $this->sortConfigValues($configValues);

        return $this->renderTemplate('configfile', array(
            'allConfigValues' => $configValues
        ));
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

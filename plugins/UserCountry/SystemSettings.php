<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp2\ServerModule;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for UserCountry.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting[] */
    public $geoIp2variables;

    protected function init()
    {
        $this->title = Piwik::translate('UserCountry_GeoIp2ServerBasedVariablesConfiguration');

        foreach (ServerModule::$defaultGeoIpServerVars as $name => $value) {
            $this->geoIp2variables[$name] = $this->createGeoIp2ServerVarSetting($name, $value);
        }
    }

    private function createGeoIp2ServerVarSetting($name, $defaultValue)
    {
        return $this->makeSetting('geoip2var_'.$name, $default = $defaultValue, FieldConfig::TYPE_STRING, function (FieldConfig $field) use ($name) {
            $field->title = Piwik::translate('UserCountry_ServerVariableFor', '<strong>' . str_replace('_', ' ', $name) . '</strong>');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
        });
    }
}

<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2;

use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2\ServerModule;
use Piwik\Plugins\UserCountry\UserCountry;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for UserCountry.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting[] */
    public $geoIp2variables;

    /** @var Setting */
    public $useCustomVars;

    protected function init()
    {
        $this->title = Piwik::translate('GeoIp2_ServerBasedVariablesConfiguration');

        $geoIpAdminEnabled = UserCountry::isGeoLocationAdminEnabled();

        $this->useCustomVars = $this->makeSetting('geoip2usecustom', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('GeoIp2_ShowCustomServerVariablesConfig');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
        $this->useCustomVars->setIsWritableByCurrentUser($geoIpAdminEnabled);

        foreach (ServerModule::$defaultGeoIpServerVars as $name => $value) {
            $this->geoIp2variables[$name] = $this->createGeoIp2ServerVarSetting($name, $value);
            $this->geoIp2variables[$name]->setIsWritableByCurrentUser($geoIpAdminEnabled);
        }
    }

    private function createGeoIp2ServerVarSetting($name, $defaultValue)
    {
        return $this->makeSetting('geoip2var_'.$name, $default = $defaultValue, FieldConfig::TYPE_STRING, function (FieldConfig $field) use ($name) {
            $field->title = Piwik::translate('GeoIp2_ServerVariableFor', '<strong>' . str_replace('_', ' ', $name) . '</strong>');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->condition = 'geoip2usecustom==1';
        });
    }
}
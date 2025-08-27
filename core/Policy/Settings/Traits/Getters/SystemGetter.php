<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Plugin\SystemSettings;

trait SystemGetter
{
    use CommonProperties;

    public static function getSystemValue()
    {
        $systemSettings = new CustomSystemSettings(); 
        $setting = new SystemSetting(
            self::getSystemName(), 
            self::getDefaultValue(), 
            self::getType(), 
            self::getPluginName()
        );
        $systemSettings->addSetting($setting);
        return $systemSettings->getSetting(self::getSystemName())->getValue();
    }

    abstract protected static function getSystemName(): string;
}

class CustomSystemSettings extends SystemSettings
{
    public function init()
    {
        // do nothing
    }
}




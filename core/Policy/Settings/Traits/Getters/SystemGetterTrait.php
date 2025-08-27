<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Settings\Plugin\SystemSetting;

trait SystemGetterTrait
{
    use CommonProperties;

    public static function getSystemValue()
    {
        $setting = new SystemSetting(
            self::getSystemName(),
            self::getDefaultValue(),
            self::getType(),
            self::getPluginName()
        );

        return $setting->getValue();
    }

    abstract protected static function getSystemName(): string;
}

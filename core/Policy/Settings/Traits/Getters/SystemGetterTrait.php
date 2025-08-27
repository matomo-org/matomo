<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Piwik;
use Piwik\Policy\Settings\SystemSettingInterface;
use Piwik\Settings\Plugin\SystemSetting;

/**
 * @phpstan-require-implements SystemSettingInterface
 */
trait SystemGetterTrait
{
    public static function getSystemSetting(): SystemSetting
    {
        return new SystemSetting(
            self::getSystemName(),
            self::getMeasurableDefaultValue(),
            self::getMeasurableType(),
            Piwik::getPluginNameOfMatomoClass(static::class)
        );
    }

    public static function getSystemValue()
    {
        return self::getSystemSetting()->getValue();
    }

    abstract protected static function getSystemDefaultValue();
    abstract protected static function getSystemName(): string;
    abstract protected static function getSystemType(): string;
}

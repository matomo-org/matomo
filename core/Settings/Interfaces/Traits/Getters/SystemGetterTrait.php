<?php

namespace Piwik\Settings\Interfaces\Traits\Getters;

use Piwik\Piwik;
use Piwik\Settings\Plugin\SystemSetting;

/**
 * @template T of mixed
 *
 * @phpstan-require-implements \Piwik\Settings\Interfaces\SystemSettingInterface<T>
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

    /**
     * @return T
     */
    public static function getSystemValue()
    {
        return self::getSystemSetting()->getValue();
    }

    /**
     * @return T
     */
    abstract protected static function getSystemDefaultValue();

    abstract protected static function getSystemName(): string;

    abstract protected static function getSystemType(): string;
}

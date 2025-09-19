<?php

namespace Piwik\Settings\Interfaces\Traits\Getters;

use Piwik\Piwik;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Settings\Measurable\MeasurableProperty;

/**
 * @template T of mixed
 *
 * @phpstan-require-implements \Piwik\Settings\Interfaces\MeasurableSettingInterface<T>
 */
trait MeasurableGetterTrait
{
    public static function getMeasurableSetting(int $idSite, bool $isProperty = false)
    {
        if ($isProperty) {
            return new MeasurableProperty(
                self::getMeasurableName(),
                self::getMeasurableDefaultValue(),
                self::getMeasurableType(),
                Piwik::getPluginNameOfMatomoClass(static::class),
                $idSite
            );
        }
        return new MeasurableSetting(
            self::getMeasurableName(),
            self::getMeasurableDefaultValue(),
            self::getMeasurableType(),
            Piwik::getPluginNameOfMatomoClass(static::class),
            $idSite
        );
    }

    /**
     * @return T
     */
    public static function getMeasurableValue(int $idSite, bool $isProperty = false)
    {
        return self::getMeasurableSetting($idSite, $isProperty)->getValue();
    }

    /**
     * @return T
     */
    abstract protected static function getMeasurableDefaultValue();

    abstract protected static function getMeasurableName(): string;

    abstract protected static function getMeasurableType(): string;
}

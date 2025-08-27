<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Piwik;
use Piwik\Policy\Settings\MeasurableSettingInterface;
use Piwik\Settings\Measurable\MeasurableSetting;

/**
 * @phpstan-require-implements MeasurableSettingInterface
 */
trait MeasurableGetterTrait
{
    public static function getMeasurableSetting(int $idSite): MeasurableSetting
    {
        return new MeasurableSetting(
            self::getMeasurableName(),
            self::getMeasurableDefaultValue(),
            self::getMeasurableType(),
            Piwik::getPluginNameOfMatomoClass(static::class),
            $idSite
        );
    }

    public static function getMeasurableValue(int $idSite)
    {
        return self::getMeasurableSetting($idSite)->getValue();
    }

    abstract protected static function getMeasurableDefaultValue();

    abstract protected static function getMeasurableName(): string;

    abstract protected static function getMeasurableType(): string;
}

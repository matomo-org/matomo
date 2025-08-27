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
    public static function getMeasurableValue(int $idSite)
    {
        $measurable = new MeasurableSetting(
            self::getMeasurableName(),
            self::getMeasurableDefaultValue(),
            self::getMeasurableType(),
            Piwik::getPluginNameOfMatomoClass(static::class),
            $idSite
        );

        return $measurable->getValue();
    }

    abstract protected static function getMeasurableDefaultValue();

    abstract protected static function getMeasurableName(): string;

    abstract protected static function getMeasurableType(): string;
}

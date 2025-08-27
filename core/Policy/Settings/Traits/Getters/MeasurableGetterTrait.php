<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Piwik;
use Piwik\Settings\Measurable\MeasurableSetting;

trait MeasurableGetterTrait
{
    use CommonProperties;

    public static function getMeasurableValue(int $idSite)
    {
        $measurable = new MeasurableSetting(
            self::getMeasurableName(),
            self::getDefaultValue(),
            self::getType(),
            Piwik::getPluginNameOfMatomoClass(static::class),
            $idSite
        );

        return $measurable->getValue();
    }

    abstract protected static function getMeasurableName(): string;
}

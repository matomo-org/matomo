<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Settings\Measurable\MeasurableSetting;

trait MeasurableGetter
{
    use CommonProperties;

    public static function getMeasurableValue(int $idSite)
    {
        $measureable = new MeasurableSetting(
            self::getMeasurableName(),
            self::getDefaultValue(),
            self::getType(),
            self::getPluginName(),
            $idSite 
        );

        return $measureable->getValue();
    }

    abstract protected static function getMeasurableName(): string;
}

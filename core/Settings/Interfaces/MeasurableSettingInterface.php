<?php

namespace Piwik\Settings\Interfaces;

use Piwik\Settings\Measurable\MeasurableProperty;
use Piwik\Settings\Measurable\MeasurableSetting;

/**
 * @template T of mixed
 */
interface MeasurableSettingInterface
{
    /**
     * @return MeasurableSetting|MeasurableProperty
     */
    public static function getMeasurableSetting(int $idSite);

    /**
     * @return T
     */
    public static function getMeasurableValue(int $idSite);
}

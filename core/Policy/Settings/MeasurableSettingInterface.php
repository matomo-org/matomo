<?php

namespace Piwik\Policy\Settings;

use Piwik\Settings\Measurable\MeasurableSetting;

/**
 * @template T of mixed
 */
interface MeasurableSettingInterface
{
    public static function getMeasurableSetting(int $idSite): MeasurableSetting;

    /**
     * @return T
     */
    public static function getMeasurableValue(int $idSite);
}

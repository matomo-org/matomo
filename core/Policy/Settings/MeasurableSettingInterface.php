<?php

namespace Piwik\Policy\Settings;

use Piwik\Settings\Measurable\MeasurableSetting;

interface MeasurableSettingInterface
{
    public static function getMeasurableSetting(int $idSite): MeasurableSetting;

    public static function getMeasurableValue(int $idSite);
}

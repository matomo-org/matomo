<?php

namespace Piwik\Policy\Settings;

interface MeasurableSettingInterface
{
    public static function getMeasurableValue(int $idSite);
}

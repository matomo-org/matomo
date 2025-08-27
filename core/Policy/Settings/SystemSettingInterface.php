<?php

namespace Piwik\Policy\Settings;

use Piwik\Settings\Plugin\SystemSetting;

interface SystemSettingInterface
{
    public static function getSystemSetting(): SystemSetting;

    public static function getSystemValue();
}

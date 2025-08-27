<?php

namespace Piwik\Policy\Settings;

use Piwik\Settings\Plugin\SystemSetting;

/**
 * @template T of mixed
 */
interface SystemSettingInterface
{
    public static function getSystemSetting(): SystemSetting;

    /**
     * @return T
     */
    public static function getSystemValue();
}

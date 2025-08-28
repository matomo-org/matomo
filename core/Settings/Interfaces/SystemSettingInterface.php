<?php

namespace Piwik\Settings\Interfaces;

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

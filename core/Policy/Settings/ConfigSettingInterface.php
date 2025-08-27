<?php

namespace Piwik\Policy\Settings;

/**
 * @template T of mixed
 */
interface ConfigSettingInterface
{
    /**
     * @return T
     */
    public static function getConfigValue();
}

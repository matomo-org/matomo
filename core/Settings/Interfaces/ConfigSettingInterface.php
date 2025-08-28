<?php

namespace Piwik\Settings\Interfaces;

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

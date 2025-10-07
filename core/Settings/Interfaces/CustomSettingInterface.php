<?php

namespace Piwik\Settings\Interfaces;

/**
 * @template T of mixed
 */
interface CustomSettingInterface
{
    /**
     * @return T
     */
    public static function getCustomValue(?int $idSite = null);
}

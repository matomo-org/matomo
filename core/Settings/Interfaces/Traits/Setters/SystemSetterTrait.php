<?php

namespace Piwik\Settings\Interfaces\Traits\Setters;

use Piwik\Settings\Interfaces\Traits\Getters\SystemGetterTrait;

/**
 * @template T of mixed
 */
trait SystemSetterTrait
{
    /**
     * @use SystemGetterTrait<T>
     */
    use SystemGetterTrait;

    /**
     * @param T $value
     */
    public static function setSystemValue($value): void
    {
        $setting = self::getSystemSetting();
        $setting->setValue($value);
        $setting->save();
    }
}

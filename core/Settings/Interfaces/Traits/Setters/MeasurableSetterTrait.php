<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces\Traits\Setters;

use Piwik\Settings\Interfaces\Traits\Getters\MeasurableGetterTrait;

/**
 * @template T of mixed
 */
trait MeasurableSetterTrait
{
    /**
     * @use MeasurableGetterTrait<T>
     */
    use MeasurableGetterTrait;

    /**
     * @param T $value
     */
    public static function setMeasurableValue(int $idSite, $value, bool $isProperty = false): void
    {
        $setting = self::getMeasurableSetting($idSite, $isProperty);
        $setting->setValue($value);
        $setting->save();
    }
}

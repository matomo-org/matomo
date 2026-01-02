<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces\Traits\Getters;

/**
 * @template T of mixed
 */
trait CustomGetterTrait
{
    /**
     * @return T
     */
    abstract protected static function getCustomValue(?int $idSite = null);

    abstract protected static function getCustomSettingName(): string;

    /**
     * @deprecated Will be removed in 6.0 in favour of making getCustomSettingName public
     */
    public static function getCustomSettingShortName(): string
    {
        return self::getCustomSettingName();
    }
}

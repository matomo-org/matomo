<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces\Traits\Getters;

use Piwik\Config;
use Piwik\Settings\Interfaces\ConfigSettingInterface;

/**
 * @template T of mixed
 *
 * @phpstan-require-implements ConfigSettingInterface
 */
trait ConfigGetterTrait
{
    /**
     * @return T|null
     */
    public static function getConfigValue()
    {
        $config = Config::getInstance()->{self::getConfigSection()};

        if (is_null($config) || !array_key_exists(self::getConfigSettingName(), $config)) {
            return null;
        }

        return $config[self::getConfigSettingName()];
    }

    abstract protected static function getConfigSection(): string;

    abstract protected static function getConfigSettingName(): string;

    /**
     * @deprecated Will be removed in 6.0 in favour of making getConfigSettingName public
     */
    public static function getConfigSettingShortName(): string
    {
        return self::getConfigSettingName();
    }
}

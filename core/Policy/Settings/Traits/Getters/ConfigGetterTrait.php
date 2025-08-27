<?php

namespace Piwik\Policy\Settings\Traits\Getters;

use Piwik\Config;
use Piwik\Policy\Settings\ConfigSettingInterface;

/**
 * @phpstan-require-implements ConfigSettingInterface
 */
trait ConfigGetterTrait
{
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
}

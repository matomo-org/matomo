<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Config;

use Piwik\Config;

abstract class SectionConfig
{
    abstract public static function getSectionName(): string;

    /**
     * Set the value for a setting
     *
     * @param string $name Setting name
     * @param mixed $value Value
     *
     * @return void
     */
    public static function setConfigValue(string $name, $value): void
    {
        $section = self::getConfig();
        $section[$name] = $value;
        Config::getInstance()->{static::getSectionName()} = $section;
    }

    /**
     * Get a setting value
     *
     * @param string    $name     Setting name
     * @param int|null  $idSite   Optional site Id
     *
     * @return mixed|null
     */
    public static function getConfigValue(string $name, ?int $idSite = null)
    {
        $config = self::getConfig();
        if (!empty($idSite)) {
            $siteSpecificConfig = self::getSiteSpecificConfig($idSite);
            $config = array_merge($config, $siteSpecificConfig);
        }
        return $config[$name] ?? null;
    }

    /**
     * Get the section config as an array
     *
     * @return array|string
     */
    private static function getConfig()
    {
        return Config::getInstance()->{static::getSectionName()};
    }

    /**
     * Get the site specific config (if any) as an array
     *
     * @param   int $idSite
     *
     * @return array|string
     */
    private static function getSiteSpecificConfig(int $idSite)
    {
        $key = static::getSectionName() . '_' . $idSite;
        return Config::getInstance()->$key;
    }
}

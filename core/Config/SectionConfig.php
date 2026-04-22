<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Config;

use Piwik\Config;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;

abstract class SectionConfig
{
    abstract public static function getSectionName(): string;

    /**
     * Set the value for a setting
     *
     * @param string $name Setting name
     * @param mixed $value Value
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
     * Prefer one of the type-safe getters if a specific type is expected:
     * @see getIntegerConfigValue()
     * @see getFloatConfigValue()
     * @see getBoolConfigValue()
     * @see getStringConfigValue()
     * @see getArrayConfigValue()
     *
     * @param string    $name     Setting name
     * @param int|null  $idSite   Optional site Id
     *
     * @return mixed|null
     */
    public static function getConfigValue(string $name, ?int $idSite = null)
    {
        return static::getRawConfigValue($name, $idSite);
    }

    /**
     * @phpstan-return ($default is null ? int|null : int)
     */
    public static function getIntegerConfigValue(string $name, ?int $default = null, ?int $idSite = null): ?int
    {
        return self::castIntConfigValue($name, static::getRawConfigValue($name, $idSite), $default);
    }

    /**
     * @phpstan-return ($default is null ? float|null : float)
     */
    public static function getFloatConfigValue(string $name, ?float $default = null, ?int $idSite = null): ?float
    {
        return self::castFloatConfigValue($name, static::getRawConfigValue($name, $idSite), $default);
    }

    /**
     * @phpstan-return ($default is null ? bool|null : bool)
     */
    public static function getBoolConfigValue(string $name, ?bool $default = null, ?int $idSite = null): ?bool
    {
        return self::castBoolConfigValue($name, static::getRawConfigValue($name, $idSite), $default);
    }

    /**
     * @phpstan-return ($default is null ? string|null : string)
     */
    public static function getStringConfigValue(string $name, ?string $default = null, ?int $idSite = null): ?string
    {
        return self::castStringConfigValue($name, static::getRawConfigValue($name, $idSite), $default);
    }

    /**
     * @return array<mixed>|null
     * @phpstan-return ($default is null ? array<mixed>|null : array<mixed>)
     */
    public static function getArrayConfigValue(string $name, ?array $default = null, ?int $idSite = null): ?array
    {
        return self::castArrayConfigValue($name, static::getRawConfigValue($name, $idSite), $default);
    }

    /**
     * @return mixed|null
     */
    protected static function getRawConfigValue(string $name, ?int $idSite = null)
    {
        $config = self::getMergedConfig($idSite);
        return $config[$name] ?? null;
    }

    /**
     * Get the section config as an array
     *
     * @return array<string, mixed>
     */
    private static function getConfig(): array
    {
        $config = Config::getInstance()->{static::getSectionName()};
        return is_array($config) ? $config : [];
    }

    /**
     * Get the site specific config (if any) as an array
     *
     * @return array<string, mixed>
     */
    private static function getSiteSpecificConfig(int $idSite): array
    {
        $key = static::getSectionName() . '_' . $idSite;
        $config = Config::getInstance()->$key;
        return is_array($config) ? $config : [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function getMergedConfig(?int $idSite = null): array
    {
        $config = self::getConfig();
        if (!empty($idSite)) {
            $config = array_merge($config, self::getSiteSpecificConfig($idSite));
        }
        return $config;
    }

    /**
     * @param mixed $value
     */
    private static function castIntConfigValue(string $name, $value, ?int $default): ?int
    {
        if ($value === null) {
            return $default;
        }

        if ((is_string($value) || is_numeric($value)) && (string) $value === (string) (int) $value) {
            return (int) $value;
        }

        self::logTypeCastWarning($name, 'int', $value);
        return $default;
    }

    /**
     * @param mixed $value
     */
    private static function castFloatConfigValue(string $name, $value, ?float $default): ?float
    {
        if ($value === null) {
            return $default;
        }

        $parsedFloat = Common::parseFloat($value);
        if ($parsedFloat !== null) {
            return $parsedFloat;
        }

        self::logTypeCastWarning($name, 'float', $value);
        return $default;
    }

    /**
     * @param mixed $value
     */
    private static function castBoolConfigValue(string $name, $value, ?bool $default): ?bool
    {
        if ($value === null) {
            return $default;
        }

        if ($value === false || $value === true) {
            return $value;
        }

        if ((is_string($value) && strtolower($value) === 'false') || $value === '0' || $value === 0) {
            return false;
        }

        if ((is_string($value) && strtolower($value) === 'true') || $value === '1' || $value === 1) {
            return true;
        }

        self::logTypeCastWarning($name, 'bool', $value);
        return $default;
    }

    /**
     * @param mixed $value
     */
    private static function castStringConfigValue(string $name, $value, ?string $default): ?string
    {
        if ($value === null) {
            return $default;
        }

        if (is_string($value) || is_numeric($value)) {
            return Common::sanitizeNullBytes((string) $value);
        }

        self::logTypeCastWarning($name, 'string', $value);
        return $default;
    }

    /**
     * @param mixed $value
     * @return array<mixed>|null
     */
    private static function castArrayConfigValue(string $name, $value, ?array $default): ?array
    {
        if ($value === null) {
            return $default;
        }

        if (is_array($value)) {
            /** @var array<mixed> $sanitizedValue */
            $sanitizedValue = self::filterNullBytes($value);
            return $sanitizedValue;
        }

        self::logTypeCastWarning($name, 'array', $value);
        return $default;
    }

    /**
     * @param mixed $value
     */
    private static function logTypeCastWarning(string $name, string $type, $value): void
    {
        StaticContainer::get(LoggerInterface::class)->warning(
            'Failed to cast config value {section}.{name} to {type}; actual type was {actualType}.',
            [
                'section' => static::getSectionName(),
                'name' => $name,
                'type' => $type,
                'actualType' => self::getValueType($value),
            ]
        );
    }

    /**
     * @param mixed $value
     */
    private static function getValueType($value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function filterNullBytes($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayValue) {
                $result[$key] = self::filterNullBytes($arrayValue);
            }

            return $result;
        }

        return is_string($value) ? Common::sanitizeNullBytes($value) : $value;
    }
}

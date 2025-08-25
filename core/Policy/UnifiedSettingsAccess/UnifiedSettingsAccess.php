<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\UnifiedSettingsAccess;

use Piwik\Policy\UnifiedSettingsAccess\Getters\ConfigSettingGetter;
use Piwik\Policy\UnifiedSettingsAccess\Getters\MeasurableSettingGetter;
use Piwik\Policy\UnifiedSettingsAccess\Getters\OptionSettingGetter;
use Piwik\Policy\UnifiedSettingsAccess\Getters\SettingGetter;
use Piwik\Policy\UnifiedSettingsAccess\Getters\SystemSettingGetter;
use Piwik\Policy\SettingValues\SettingValue;

class UnifiedSettingsAccess
{
    public const SOURCE_CONFIG = 'config';
    public const SOURCE_OPTION = 'option';
    public const SOURCE_MEASURABLE = 'measurable';
    public const SOURCE_SYSTEM = 'system';

    public const TYPE_BOOL = 'boolean';
    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';
    public const TYPE_ARRAY = 'array';

    private static $sourceToClassMap = [
        self::SOURCE_MEASURABLE => MeasurableSettingGetter::class,
        self::SOURCE_SYSTEM => SystemSettingGetter::class,
        self::SOURCE_CONFIG => ConfigSettingGetter::class,
        self::SOURCE_OPTION => OptionSettingGetter::class,
    ];

    public static $defaultHierarchy = [self::SOURCE_MEASURABLE, self::SOURCE_SYSTEM, self::SOURCE_CONFIG];

    public static function getSetting(string $setting, string $type, mixed $defaultValue, ?int $idSite = null, ?array $hierarchy = null): ?SettingValue
    {
        if (is_null($hierarchy)) {
            $hierarchy = static::$defaultHierarchy;
        }

        [$pluginName, $settingName] = explode('.', $setting, 2);

        foreach ($hierarchy as $hierarchyKey) {
            /** @var SettingGetter $getterClass */
            $getterClass = self::$sourceToClassMap[$hierarchyKey];
            $getter = new $getterClass($pluginName, $settingName, $type, $defaultValue, $idSite);

            try {
                if ($getter->hasSetting()) {
                    return $getter->getSetting();
                } else {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}

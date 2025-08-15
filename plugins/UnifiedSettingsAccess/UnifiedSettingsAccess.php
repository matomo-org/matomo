<?php

namespace Piwik\Plugins\UnifiedSettingsAccess;

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
        self::SOURCE_SYSTEM => SystemSettingGetter::class,
        self::SOURCE_CONFIG => ConfigSettingGetter::class,
        self::SOURCE_MEASURABLE => MeasurableSettingGetter::class,
    ];

    public static $defaultHierarchy = [self::SOURCE_MEASURABLE, self::SOURCE_SYSTEM, self::SOURCE_CONFIG];

    public function getSetting(
        string $setting,
        $defaultValue = null,
        string $type = self::TYPE_STRING,
        int $idSite = null,
        array $hierarchy = null
    )
    {
        [$pluginName, $settingName] = explode('.', $setting);

        if (null === $hierarchy) {
            $hierarchy = static::$defaultHierarchy;
        }

        foreach ($hierarchy as $hierarchyKey) {
            /** @var SettingGetter $getterClass */
            $getterClass = self::$sourceToClassMap[$hierarchyKey];
            $getter = new $getterClass($pluginName, $settingName, $type, $defaultValue, $idSite);

            try {
                return $getter->getSetting();
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}

<?php

namespace Piwik\Policy;

use Exception;
use Piwik\Plugin\Manager;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Setters\MeasurableSetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\SystemSetterTrait;

/**
 * @implements SystemSettingInterface<bool>
 * @implements MeasurableSettingInterface<bool>
 */
abstract class CompliancePolicy implements SystemSettingInterface, MeasurableSettingInterface
{
    /**
     * @use SystemSetterTrait<bool>
     */
    use SystemSetterTrait;

    /**
     * @use MeasurableSetterTrait<bool>
     */
    use MeasurableSetterTrait;

    abstract public static function getName(): string;
    abstract public static function getDescription(): string;
    abstract public static function getTitle(): string;

    /**
     * @return array<string> of plugin names that are required for this policy to function
     */
    abstract protected static function getMinimumRequiredPlugins(): array;

    /**
     * @return array<string, string>
     */
    public static function getDetails(): array
    {
        return [
            'id' => static::getName(),
            'title' => static::getTitle(),
            'description' => static::getDescription(),
        ];
    }

    /**
     * @throws \Exception when required plugins are not active
     */
    protected static function checkRequiredPluginsActive(): void
    {
        $plugins = static::getMinimumRequiredPlugins();
        $pluginManager = static::getPluginManagerInstance();

        foreach ($plugins as $plugin) {
            if (!$pluginManager->isPluginActivated($plugin)) {
                throw new Exception("Plugin $plugin is not activated");
            }
        }
    }

    protected static function getPluginManagerInstance(): Manager
    {
        return Manager::getInstance();
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getSystemName(): string
    {
        return preg_replace('/\s+/', '', static::getName()) . '_policy_enabled';
    }

    protected static function getSystemType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    protected static function getMeasurableDefaultValue()
    {
        return false;
    }

    protected static function getMeasurableName(): string
    {
        return preg_replace('/\s+/', '', static::getName()) . '_policy_enabled';
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    /**
     * If the policy is active at the instance level,
     * disabling the policy for a site will also disable it
     * for the instance.
     */
    public static function setActiveStatus(?int $idSite, bool $isActive): void
    {
        if (isset($idSite)) {
            static::setMeasurableValue($idSite, $isActive);
            if (static::getSystemValue() && !$isActive) {
                static::setSystemValue($isActive);
            }
            return;
        }
        static::setSystemValue($isActive);
    }

    /**
     * If the policy is active at the instance level, then
     * this function will return true for all sites.
     */
    public static function isActive(?int $idSite): bool
    {
        try {
            self::checkRequiredPluginsActive();
        } catch (Exception $e) {
            return false;
        }

        $instanceLevel = static::getSystemValue();
        if (!$instanceLevel && isset($idSite)) {
            return static::getMeasurableValue($idSite);
        }
        return $instanceLevel;
    }
}

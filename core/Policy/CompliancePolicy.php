<?php

namespace Piwik\Policy;

use Piwik\Plugin\Manager;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Interfaces\MeasurableSettingInterface;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;
use Piwik\Settings\Interfaces\SystemSettingInterface;
use Piwik\Settings\Interfaces\Traits\Setters\MeasurableSetterTrait;
use Piwik\Settings\Interfaces\Traits\Setters\SystemSetterTrait;

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

    /**
     * @return array<class-string<PolicyComparisonInterface<mixed>&SettingValueInterface<mixed>>>
     */
    public static function getAllControlledSettings(?int $idSite = null): array
    {
        $settings = self::getAllSettings($idSite);
        $underPolicy = [];

        foreach ($settings as $setting) {
            if (!$setting::isControlledBySpecificPolicy(static::class, $idSite)) {
                continue;
            }

            $underPolicy[] = $setting;
        }

        return $underPolicy;
    }

    /**
     * @return array<class-string<PolicyComparisonInterface<mixed>&SettingValueInterface<mixed>>>
     */
    public static function getAllSettings(?int $idSite = null): array
    {
        $settings = Manager::getInstance()->findMultipleComponents('Settings', SettingValueInterface::class);
        $underPolicy = [];

        foreach ($settings as $setting) {
            if (!is_a($setting, PolicyComparisonInterface::class, true)) {
                continue;
            }

            $underPolicy[] = $setting;
        }

        return $underPolicy;
    }

    protected static function getSystemDefaultValue()
    {
        return false;
    }

    protected static function getSystemName(): string
    {
        return 'cnil_policy_enabled';
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
        return 'cnil_policy_enabled';
    }

    protected static function getMeasurableType(): string
    {
        return FieldConfig::TYPE_BOOL;
    }

    public static function setActiveStatus(?int $idSite, bool $isActive): void
    {
        if (isset($idSite)) {
            self::setMeasurableValue($idSite, $isActive);
            return;
        }
        self::setSystemValue($isActive);
    }

    public static function isActive(?int $idSite): bool
    {
        if (isset($idSite)) {
            return self::getMeasurableValue($idSite);
        }
        return self::getSystemValue();
    }
}

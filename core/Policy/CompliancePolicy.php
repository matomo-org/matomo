<?php

namespace Piwik\Policy;

use Piwik\Plugin\Manager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Settings\Interfaces\SettingValueInterface;

abstract class CompliancePolicy
{
    abstract public static function getName(): string;
    abstract public static function getDescription(): string;
    abstract public static function isActive(?int $idSite): bool;

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
}

<?php

namespace Piwik\Policy\Policies;

use Piwik\Plugin\Manager;
use Piwik\Policy\Settings\PolicyComparisonInterface;
use Piwik\Policy\Settings\SettingValueInterface;

abstract class CompliancePolicy
{
    abstract public static function getName(): string;
    abstract public static function getDescription(): string;
    abstract public static function isActive(?int $idSite): bool;

    /**
     * @return array<class-string>
     */
    public static function getAllSettings(?int $idSite = null): array
    {
        $settings = Manager::getInstance()->findMultipleComponents('Settings', SettingValueInterface::class);
        $underPolicy = [];

        foreach ($settings as $setting) {
            if (!is_a($setting, PolicyComparisonInterface::class, true)) {
                continue;
            }

            if (!$setting::isControlledBySpecificPolicy(static::getName(), $idSite)) {
                continue;
            }

            $underPolicy[] = $setting;
        }

        return $underPolicy;
    }
}

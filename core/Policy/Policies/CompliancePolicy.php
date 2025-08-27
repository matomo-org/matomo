<?php

namespace Piwik\Policy\Policies;

use Piwik\Plugin\Manager;
use Piwik\Policy\Settings\ISettingValue;

abstract class CompliancePolicy
{
    abstract public static function getName(): string;
    abstract public static function getDescription(): string;
    abstract public static function isActive(?int $idSite): bool;

    public static function getAllSettings()
    {
        $settings = Manager::getInstance()->findMultipleComponents('Settings', ISettingValue::class);
        
        $underPolicy = [];
        foreach ($settings as $setting) {
            if (method_exists($setting, 'isControlledBySpecificPolicy')) {
                if ($setting::isControlledBySpecificPolicy(static::getName())) {
                    $underPolicy[] = $setting;
                }
            }
        }
        return $settings;
    }
}

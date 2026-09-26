<?php

namespace Piwik\Tests\Framework\Mock\Policy;

use Piwik\Tests\Framework\Mock\Settings\FakePolicySetting;

class PolicyManager extends \Piwik\Policy\PolicyManager
{
    /**
     * The settings a test pretends are discovered, in discovery order.
     *
     * @var array<class-string>|null
     */
    private static $discoveredSettings = null;

    public static function getAllPolicies(): array
    {
        return [
            TestPolicy::class,
        ];
    }

    /**
     * @param array<class-string>|null $settings in discovery order, null restoring the default
     */
    public static function setDiscoveredSettings(?array $settings): void
    {
        self::$discoveredSettings = $settings;
    }

    protected static function getAllSettings(?string $settingType = null): array
    {
        return self::$discoveredSettings ?? [FakePolicySetting::class];
    }
}

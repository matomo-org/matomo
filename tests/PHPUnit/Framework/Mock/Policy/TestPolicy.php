<?php

namespace Piwik\Tests\Framework\Mock\Policy;

use Piwik\Tests\Framework\Mock\Plugin\Manager as MockManager;
use Piwik\Plugin\Manager;

class TestPolicy extends \Piwik\Policy\CompliancePolicy
{
    /** @var bool */
    private static $system = false;

    /** @var array<int,bool> */
    private static $perSite = [];

    public static function reset(): void
    {
        self::$system = false;
        self::$perSite = [];
    }

    public static function getName(): string
    {
        return 'test_policy_v1';
    }

    public static function getDescription(): string
    {
        return 'Test policy description';
    }

    public static function getTitle(): string
    {
        return 'Test Policy';
    }

    protected static function getMinimumRequiredPlugins(): array
    {
        return [];
    }

    public static function getSystemValue()
    {
        return self::$system;
    }

    public static function setSystemValue($value): void
    {
        self::$system = (bool) $value;
    }

    public static function getMeasurableValue($idSite, $isProperty = null)
    {
        return self::$perSite[$idSite] ?? false;
    }

    public static function setMeasurableValue($idSite, $value, $isProperty = null): void
    {
        self::$perSite[$idSite] = (bool) $value;
    }

    protected static function getPluginManagerInstance(): Manager
    {
        $manager = new MockManager();
        $manager->setActivatedPlugins([]);
        return $manager;
    }

    public static function setState($instanceLevel, $siteLevel)
    {
        self::$system = $instanceLevel;
        if (is_int($siteLevel)) {
            self::$perSite[$siteLevel] = true;
        } else {
            self::$perSite = [];
        }
    }
}

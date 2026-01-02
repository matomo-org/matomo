<?php

namespace Piwik\Tests\Framework\Mock\Policy;

use Piwik\Tests\Framework\Mock\Settings\FakePolicySetting;

class PolicyManager extends \Piwik\Policy\PolicyManager
{
    public static function getAllPolicies(): array
    {
        return [
            TestPolicy::class,
        ];
    }

    protected static function getAllSettings(?string $settingType = null): array
    {
        $settings[] = FakePolicySetting::class;
        return $settings;
    }
}

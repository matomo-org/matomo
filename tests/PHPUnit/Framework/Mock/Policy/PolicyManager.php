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

    public static function getAllSettings(?int $idSite = null): array
    {
        $settings[] = FakePolicySetting::class;
        return $settings;
    }
}

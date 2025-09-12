<?php

namespace Piwik\tests\PHPUnit\Framework\Mock\Policy;

use Piwik\Tests\Framework\Mock\Settings\FakePolicySetting;

class PolicyManager extends \Piwik\Policy\PolicyManager
{
    public static function getAllPolicies(): array
    {
        return [
            TestPolicy::class
        ];
    }
    
    public static function getAllSettings(?int $idSite = null): array
    {
        $settings = parent::getAllSettings($idSite);
        $settings[] = FakePolicySetting::class;
        return $settings;

    }
}

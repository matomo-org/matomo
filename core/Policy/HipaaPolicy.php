<?php

namespace Piwik\Policy;

class HipaaPolicy extends CompliancePolicy
{
    public static function getName(): string
    {
        return 'hipaa_v1';
    }

    public static function getDescription(): string
    {
        return 'test description';
    }

    public static function getTitle(): string
    {
        return 'HIPAA';
    }

    protected static function getMinimumRequiredPlugins(): array
    {
        return [
            'PrivacyManager',
            'Live',
            'WebsiteMeasurable',
        ];
    }
}

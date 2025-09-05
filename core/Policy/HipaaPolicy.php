<?php

namespace Piwik\Policy;

use Piwik\Settings\FieldConfig;

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
}

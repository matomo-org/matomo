<?php

namespace Piwik\Tests\Framework\Mock\Policy;

/**
 * A policy that provides its own copy for the granular per-setting table.
 */
class GranularTestPolicy extends TestPolicy
{
    public static function getName(): string
    {
        return 'granular_test_policy_v1';
    }

    protected static function generateGranularDescription(): string
    {
        return 'Granular test policy description';
    }

    protected static function generateWarnings(): string
    {
        return 'Test policy warning';
    }
}

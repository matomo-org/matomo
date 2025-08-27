<?php

namespace Piwik\Policy\Policies;

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

    public static function isActive(?int $idSite): bool
    {
        if (null === $idSite) {
            return true;
        }

        if (1 === $idSite) {
            return false;
        }

        return true;
    }
}

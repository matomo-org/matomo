<?php

namespace Piwik\Policy;

class CnilPolicy extends CompliancePolicy
{
    public static function getName(): string
    {
        return 'cnil_v1';
    }

    public static function getDescription(): string
    {
        return 'test description';
    }
}

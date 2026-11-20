<?php

namespace Piwik\Tests\Framework\Mock\Settings\TraitImpls;

/**
 * A policy setting that is managed outside of the compliance page, and therefore stores no
 * enforcement state of its own.
 */
class ExternallyManagedPolicyComparisonTraitImpl extends PolicyComparisonTraitImpl
{
    public static function isExternallyManagedByPolicyPage(): bool
    {
        return true;
    }
}

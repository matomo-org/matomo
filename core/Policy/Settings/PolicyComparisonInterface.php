<?php

namespace Piwik\Policy\Settings;

interface PolicyComparisonInterface
{
    /**
     * @return array<string, mixed>
     */
    public static function getPolicyValues(?int $idSite = null): array;

    public static function isControlledBySpecificPolicy(string $policy, ?int $idSite = null): bool;
}

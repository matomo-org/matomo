<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

interface CompliancePolicy
{
    public function key(): string;
    public function isActiveFor(?int $idSite): bool;
    public function getRetentionOverride(?int $idSite): ?RetentionOverride;

}
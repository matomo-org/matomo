<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

/**
 * connection between the policy and its representation in the db
 */
final class PolicyStateRepository
{
    public function isEnabled(?int $idSite, string $policy): bool
    {
        // TODO: implement DB lookup for site-specific or global enablement.
        return false;
    }

    /**
     * @return array<string,mixed>
     */
    public function getPolicyMetadata(?int $idSite, string $policy): array
    {
        // TODO: implement DB lookup for metadata json.
        return [];
    }
}

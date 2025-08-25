<?php

namespace Piwik\Policy\Compliance;

/**
 * connection between the policy and its representation in the db
 */
final class PolicyStateRepository
{
    public function isEnabled(?int $idSite, string $policy): bool
    {
        // TODO set up Option storage and retrieval
        switch ($policy) {
            case 'cnil_v1':
                return true;
            case 'hipaa_v1':
                return true;
            default:
                return false;
            }
    }
}

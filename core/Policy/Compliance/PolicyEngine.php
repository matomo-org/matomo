<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

final class PolicyEngine
{
    /** @var CompliancePolicy[] */
    private $policies;

    /**
     * @param CompliancePolicy[] $policies
     */
    public function __construct(array $policies)
    {
        $this->policies = $policies;
    }

    public function getRetentionOverride(?int $idSite): ?RetentionOverride
    {
        $chosen = null;

        foreach ($this->policies as $p) {
            $ovr = $p->getRetentionOverride($idSite);
            if ($ovr === null) {
                continue;
            }
            if ($chosen === null || $ovr->days < $chosen->days) {
                $chosen = $ovr; // most restrictive (fewest days) wins
            }
        }

        return $chosen;
    }
}
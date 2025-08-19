<?php

declare(strict_types=1);

namespace Piwik\Policy\Cnil;

use Piwik\Policy\Compliance\PolicyStateRepository;
use Piwik\Policy\Compliance\RetentionOverride;
use Piwik\Policy\Compliance\CompliancePolicy;

final class CnilPolicy implements CompliancePolicy
{
    /** @var PolicyStateRepository */
    private $repo;

    public function __construct(PolicyStateRepository $repo)
    {
        $this->repo = $repo;
    }

    public function key(): string
    {
        return 'cnil_v1';
    }

    public function isActiveFor(?int $idSite): bool
    {
        return $this->repo->isEnabled($idSite, $this->key());
    }

    public function getRetentionOverride(?int $idSite): ?RetentionOverride
    {
        if (!$this->isActiveFor($idSite)) {
            return null;
        }

        $meta = $this->repo->getPolicyMetadata($idSite, $this->key());
        $days = isset($meta['retentionDays']) ? (int)$meta['retentionDays'] : 395; // PoC default

        return new RetentionOverride($days, 'Enforced by CNIL policy');
    }
}

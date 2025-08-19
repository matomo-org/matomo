<?php

declare(strict_types=1);

namespace Piwik\Policy\Retention;

use Piwik\Policy\Compliance\PolicyEngine;

final class RetentionService
{
    /** @var PolicyEngine */
    private $engine;

    /** @var RetentionSources */
    private $sources;

    public function __construct(
        PolicyEngine $engine,
        RetentionSources $sources
    ) {
        $this->engine = $engine;
        $this->sources = $sources;
    }

    public function getEffectiveRetentionDays(?int $idSite): RetentionDecision
    {
        $decision = null;

        $v = $this->sources->config();
        if ($v !== null) {
            $decision = new RetentionDecision($v, false, null, null, 'config');
        }

        $v = $this->sources->site($idSite);
        if ($v !== null) {
            $decision = new RetentionDecision($v, false, null, null, 'site');
        }

        $ovr = $this->engine->getRetentionOverride($idSite);
        if ($ovr !== null) {
            $decision = new RetentionDecision($ovr->days, true, 'cnil_v1', $ovr->reason, 'compliance');
        }

        if ($decision === null) {
            $decision = new RetentionDecision(365, false, null, null, 'config');
        }

        return $decision;
    }

}

<?php

declare(strict_types=1);

namespace Piwik\Policy\Policies;

use Piwik\Policy\Compliance\PolicyStateRepository;
use Piwik\Policy\Compliance\CompliancePolicy;
use Piwik\Policy\Settings\ReportRetentionSettingValue;
use Piwik\Policy\SettingValue;

class CnilPolicy extends CompliancePolicy
{
    /** @var PolicyStateRepository */
    private $repo;

    public function __construct(PolicyStateRepository $repo)
    {
        parent::__construct();
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

    protected function loadSettings(): void
    {
        $this->settings['PrivacyManager.ReportRetentionPeriod'] = ReportRetentionSettingValue::class;
    }

    protected function retrieveSettingValue(string $setting, ?int $idSite): SettingValue
    {
        // TODO
        return new ReportRetentionSettingValue();
    }
}

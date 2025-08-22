<?php

namespace Piwik\Policy\Policies;

use Piwik\Policy\Compliance\CompliancePolicy;
use Piwik\Policy\Compliance\PolicySettingValue;
use Piwik\Policy\Retention\RetentionSettingValue;

class HipaaPolicy extends CompliancePolicy
{

    public function __construct()
    {
        parent::__construct();
    }

    public function key(): string
    {
        return 'hipaa_v1';
    }

    public function isActiveFor(?int $idSite): bool
    {
        
    }

    protected function loadSettings(): void
    {
        $this->settings['PrivacyManager.ReportRetentionPeriod'] = RetentionSettingValue::class;
    }

    protected function retrieveSettingValue(string $setting): PolicySettingValue
    {
        
    }
}

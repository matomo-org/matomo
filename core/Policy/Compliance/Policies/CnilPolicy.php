<?php

namespace Piwik\Policy\Compliance\Policies;

use Piwik\Policy\Compliance\PolicyStateRepository;
use Piwik\Policy\Compliance\CompliancePolicy;
use Piwik\Policy\SettingValues\ReportRetention;
use Piwik\Policy\SettingValues\SettingValue;

class CnilPolicy extends CompliancePolicy
{
    /** @var PolicyStateRepository */
    private $repo;

    public function __construct(PolicyStateRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    public function setKey()
    {
        $this->key = 'cnil_v1';
    }

    public function isActiveFor(?int $idSite): bool
    {
        return $this->repo->isEnabled($idSite, $this->getKey());
    }

    protected function loadSettings(): void
    {
        $this->settings['Deletelogs.delete_logs_older_than'] = [ReportRetention::class, '90', ''];
    }

    protected function retrieveSettingValue(string $setting, ?int $idSite): SettingValue
    {
        [$class, $value, $notes] = $this->settings[$setting];
        return new $class($idSite, $value, $notes);
    }
}

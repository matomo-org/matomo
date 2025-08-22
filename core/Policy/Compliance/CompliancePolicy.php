<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

use Piwik\Policy\SettingValue;

/**
 * class which describes a policy, which is used to determine if a 
 * specific setting is related to said policy
 */
abstract class CompliancePolicy
{
    /* @var array<string, Class> */
    protected $settings;
    
    public function __construct()
    {
        $this->loadSettings(); 
    }
    public abstract function key(): string;
    public abstract function isActiveFor(?int $idSite): bool;
    protected abstract function retrieveSettingValue(string $setting, ?int $idSite): SettingValue;

    public function getSetting(string $setting, ?int $idSite = null): ?SettingValue
    {
        if (!$this->isActiveFor($idSite)) {
            return null;
        }
        return $this->retrieveSettingValue($setting, $idSite);
    }

    protected abstract function loadSettings(): void;

    public function hasSetting(string $setting, ?int $idSite = null): bool
    {
        return array_key_exists($setting, $this->settings);
    }

}

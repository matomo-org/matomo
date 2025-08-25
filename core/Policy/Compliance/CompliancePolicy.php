<?php

namespace Piwik\Policy\Compliance;

use Piwik\Policy\SettingValues\SettingValue;

/**
 * class which describes a policy, which is used to determine if a 
 * specific setting is related to said policy
 */
abstract class CompliancePolicy
{
    /* @var array<string, Class> */
    protected $settings;

    /** @var string */
    protected $key;
    
    public function __construct()
    {
        $this->loadSettings(); 
        $this->setKey();
    }    
    
    public abstract function setKey();

    public function getKey(): string
    {
        return $this->key;
    }

    public function hasSetting(string $setting): bool
    {
        return array_key_exists($setting, $this->settings);
    }

    public function getSetting(string $setting, ?int $idSite = null): ?SettingValue
    {
        if (!$this->isActiveFor($idSite)) {
            return null;
        }
        return $this->retrieveSettingValue($setting, $idSite);
    }

    public abstract function isActiveFor(?int $idSite): bool;
    protected abstract function loadSettings(): void;
    protected abstract function retrieveSettingValue(string $setting, ?int $idSite): SettingValue;


}

<?php

namespace Piwik\Policy\Settings;
use Piwik\Policy\SettingValue;

class ReportRetentionSettingValue implements SettingValue
{
    public function compare(?SettingValue $setting): self
    {
        // TODO
        return $this; 
    }
}

<?php

namespace Piwik\Policy\SettingValues;

class GenericSettingValue extends SettingValue
{
    public function compare(?SettingValue $setting): self
    {
        return $this;
    }
}

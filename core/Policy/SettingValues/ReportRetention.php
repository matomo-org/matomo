<?php

namespace Piwik\Policy\SettingValues;

class ReportRetention extends SettingValue
{
    public function compare(?SettingValue $setting): self
    {
        // TODO
        if (is_null($setting)) {
            return $this;
        }

        if ($this->getValue() <= $setting->getValue()) {
            return $this;
        }

        return $setting;
    }
}

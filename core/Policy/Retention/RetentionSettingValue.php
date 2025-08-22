<?php

namespace Piwik\Policy\Retention;

use Piwik\Policy\SettingValue;

class RetentionSettingValue implements SettingValue {

    public function compare($setting)
    {
        return $this;
    }
}

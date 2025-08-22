<?php

namespace Piwik\Policy;

interface SettingValue
{
    /**
     * @param ?static $setting
     * @return static
     */
    public function compare($setting);
}

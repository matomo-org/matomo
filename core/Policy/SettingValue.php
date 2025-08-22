<?php

namespace Piwik\Policy;

interface SettingValue
{
    public function compare(?self $setting): self;
}

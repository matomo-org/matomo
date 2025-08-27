<?php

namespace Piwik\Policy\Settings;

interface ISettingValue
{
    public static function getInstance(?int $idSite = null): self;
    public function getValue();
}

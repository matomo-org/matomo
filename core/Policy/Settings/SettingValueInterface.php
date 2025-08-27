<?php

namespace Piwik\Policy\Settings;

interface SettingValueInterface
{
    public static function getInstance(?int $idSite = null);

    public function getValue();
}

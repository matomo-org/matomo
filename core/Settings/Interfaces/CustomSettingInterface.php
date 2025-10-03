<?php

namespace Piwik\Settings\Interfaces;

interface CustomSettingInterface
{
    public static function getCustomValue(?int $idSite = null);
}

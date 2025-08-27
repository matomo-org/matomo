<?php

namespace Piwik\Policy\Settings;

interface OptionSettingInterface
{
    /**
     * @return string|false
     */
    public static function getOptionValue();
}

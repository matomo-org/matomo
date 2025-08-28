<?php

namespace Piwik\Settings\Interfaces;

interface OptionSettingInterface
{
    /**
     * @return string|false
     */
    public static function getOptionValue();
}

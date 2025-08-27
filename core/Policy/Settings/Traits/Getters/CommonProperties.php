<?php

namespace Piwik\Policy\Settings\Traits\Getters;

trait CommonProperties
{
    abstract protected static function getDefaultValue();
    abstract protected static function getType(): string;
    abstract protected static function getPluginName(): string;
}

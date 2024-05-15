<?php

namespace Piwik\Plugins\FeatureFlags;

abstract class Feature
{
    abstract public function getName(): string;

    public static function getInstance(): Feature
    {
        return new static();
    }
}

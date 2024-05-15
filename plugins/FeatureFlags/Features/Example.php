<?php

namespace Piwik\Plugins\FeatureFlags\Features;

use Piwik\Plugins\FeatureFlags\Feature;

class Example extends Feature
{
    public function getName(): string
    {
        return 'Example';
    }
}

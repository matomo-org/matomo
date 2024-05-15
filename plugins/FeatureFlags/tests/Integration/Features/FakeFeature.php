<?php

namespace Piwik\Plugins\FeatureFlags\tests\Integration\Features;

use Piwik\Plugins\FeatureFlags\Feature;

class FakeFeature extends Feature
{
    public function getName(): string
    {
        return 'NotReal';
    }
}

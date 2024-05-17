<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\tests\Integration\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

class FakeFeatureFlag implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'NotReal';
    }
}

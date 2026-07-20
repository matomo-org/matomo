<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;
use Piwik\Plugins\FeatureFlags\ForcedFeatureFlagStateInterface;

class PrivacyCompliance implements FeatureFlagInterface, ForcedFeatureFlagStateInterface
{
    public function getName(): string
    {
        return 'PrivacyCompliance';
    }

    public function getForcedFeatureFlagState(): bool
    {
        return true;
    }
}

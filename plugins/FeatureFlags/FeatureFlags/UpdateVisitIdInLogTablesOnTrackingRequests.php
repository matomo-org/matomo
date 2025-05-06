<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

/**
 * This will add additional load on tracking requests, it should result in more accurate reports.
 * There is a risk of table locks, hence this feature being flagged for test purposes only.
 */
class UpdateVisitIdInLogTablesOnTrackingRequests implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'UpdateVisitIdInLogTablesOnTrackingRequests';
    }
}
<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

/**
 * Gates the goal recommendation feature (scan, saved results and the
 * "Recommended goals" block on the manage goals page).
 *
 * Enable per instance with `./console featureflags:enable GoalRecommendations`.
 */
class GoalRecommendations implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'GoalRecommendations';
    }
}

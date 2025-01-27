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

/**
 * PLEASE NOTE!
 *
 * This feature flag only controls if the Config ID randomisation setting is visible in the Privacy settings.
 *
 * Disabling the feature flag once the privacy setting was enabled won't stop the config ID randomisation unless
 * disabled, either through the UI with the feature flag enabled or by removing the option from the db.
 *
 */
class ConfigIdRandomisation implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'ConfigIdRandomisation';
    }
}

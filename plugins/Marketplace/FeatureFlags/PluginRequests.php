<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

/**
 * Hides the install requests and the superuser decisions on plugin requests until the Manage Requests screens ship
 */
class PluginRequests implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'PluginRequests';
    }
}

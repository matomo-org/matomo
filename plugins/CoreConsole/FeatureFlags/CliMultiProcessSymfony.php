<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

class CliMultiProcessSymfony implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'CliMultiProcessSymfony';
    }
}

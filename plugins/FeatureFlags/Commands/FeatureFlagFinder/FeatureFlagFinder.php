<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\Commands\FeatureFlagFinder;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;
use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

class FeatureFlagFinder
{
    /**
     * @internal
     */
    public static function findFeatureFlagByName(string $name): ?FeatureFlagInterface
    {
        $directoryToCheck = StaticContainer::get('featureflag.dir_of_feature_flags');
        $featureFlagClasses = Manager::getInstance()->findMultipleComponents($directoryToCheck, FeatureFlagInterface::class);

        foreach ($featureFlagClasses as $featureFlagClass) {
            if ((new $featureFlagClass())->getName() === $name) {
                return new $featureFlagClass();
            }
        }

        return null;
    }
}
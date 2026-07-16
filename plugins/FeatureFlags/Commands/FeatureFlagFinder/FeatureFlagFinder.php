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
        foreach (self::findAll() as $featureFlag) {
            if ($featureFlag->getName() === $name) {
                return $featureFlag;
            }
        }

        return null;
    }

    /**
     * @internal
     * @return FeatureFlagInterface[]
     */
    public static function findAll(): array
    {
        $directoryToCheck = StaticContainer::get('featureflag.dir_of_feature_flags');
        $featureFlagClasses = Manager::getInstance()->findMultipleComponents($directoryToCheck, FeatureFlagInterface::class);

        $featureFlags = [];
        foreach ($featureFlagClasses as $featureFlagClass) {
            $featureFlags[] = new $featureFlagClass();
        }

        return $featureFlags;
    }
}

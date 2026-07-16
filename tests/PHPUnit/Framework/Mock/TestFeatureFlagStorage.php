<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Framework\Mock;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;
use Piwik\Plugins\FeatureFlags\FeatureFlagStorageInterface;

/**
 * Feature flag storage used in the test environment to force-enable selected feature
 * flags for all test runs without writing them to the config. Mutating the config would
 * make the flags surface as "changed values" on the Diagnostics config-file page and
 * therefore on its UI test screenshot.
 *
 * Appended to the `featureflag.storages` cascade in config/environment/test.php; since
 * later storages override earlier ones, the flags listed here win over the config storage.
 */
class TestFeatureFlagStorage implements FeatureFlagStorageInterface
{
    /**
     * @var string[]
     */
    private $enabledFlagNames;

    /**
     * @param string[] $enabledFlagNames Names (as returned by FeatureFlagInterface::getName())
     *                                   of the flags to report as enabled.
     */
    public function __construct(array $enabledFlagNames)
    {
        $this->enabledFlagNames = $enabledFlagNames;
    }

    public function isFeatureActive(FeatureFlagInterface $feature): ?bool
    {
        if (in_array($feature->getName(), $this->enabledFlagNames, true)) {
            return true;
        }

        // not handled by this storage: defer to the other storages in the cascade
        return null;
    }

    public function disableFeatureFlag(FeatureFlagInterface $feature): void
    {
        // no-op: this storage is read-only
    }

    public function enableFeatureFlag(FeatureFlagInterface $feature): void
    {
        // no-op: this storage is read-only
    }

    public function deleteFeatureFlag(string $featureName): void
    {
        // no-op: this storage is read-only
    }
}

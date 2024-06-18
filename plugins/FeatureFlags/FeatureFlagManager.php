<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags;

use Piwik\Log\Logger;
use Piwik\Log\LoggerInterface;

class FeatureFlagManager
{
    /**
     * @var FeatureFlagStorageInterface[]
     */
    private $storages;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(array $storages, LoggerInterface $logger)
    {
        $this->storages = $storages;
        $this->logger = $logger;
    }

    /**
     * @param string $featureFlag The ::class name of a class that implements FeatureFlagInterface
     * @return bool
     */
    public function isFeatureActive(string $featureFlag): bool
    {
        $featureFlagObj = $this->createFeatureFlagObjFromString($featureFlag);

        if ($featureFlagObj === null) {
            return false;
        }

        $featureActive = false;

        foreach ($this->storages as $storage) {
            $isActive = $storage->isFeatureActive($featureFlagObj);

            if ($isActive !== null) {
                $featureActive = $isActive;
            }
        }

        return $featureActive;
    }

    private function createFeatureFlagObjFromString(string $featureFlag): ?FeatureFlagInterface
    {
        if (!is_subclass_of($featureFlag, FeatureFlagInterface::class)) {
            $this->logger->debug(
                'isFeatureActive failed due to class not implementing FeatureFlagInterface',
                [
                    'featureFlag' => $featureFlag
                ]
            );
            return null;
        }

        return new $featureFlag();
    }
}

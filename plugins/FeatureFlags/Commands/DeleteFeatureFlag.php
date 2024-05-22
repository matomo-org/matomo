<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\FeatureFlags\FeatureFlagStorageInterface;

class DeleteFeatureFlag extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('featureflags:delete');
        $this->setDescription('Deletes a given feature flag');
        $this->addRequiredArgument('featureFlagName');
    }

    protected function doExecute(): int
    {
        $featureFlagName = $this->getInput()->getArgument('featureFlagName');

        if ($featureFlagName === null) {
            throw new \Exception("Feature flag could not be found");
        }

        /** @var FeatureFlagStorageInterface $storage */
        foreach (StaticContainer::get('featureflag.storages') as $storage) {
            $storage->deleteFeatureFlag($featureFlagName);
        }

        return self::SUCCESS;
    }
}

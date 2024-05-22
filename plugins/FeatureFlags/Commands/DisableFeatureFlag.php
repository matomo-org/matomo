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
use Piwik\Plugins\FeatureFlags\Commands\FeatureFlagFinder\FeatureFlagFinder;
use Piwik\Plugins\FeatureFlags\FeatureFlagStorageInterface;

class DisableFeatureFlag extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('featureflags:disable');
        $this->setDescription('Disables a given feature flag');
        $this->addRequiredArgument('featureFlagName');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $featureFlag = FeatureFlagFinder::findFeatureFlagByName($input->getArgument('featureFlagName'));

        if ($featureFlag === null) {
            throw new \Exception("Feature flag could not be found");
        }

        /** @var FeatureFlagStorageInterface $storage */
        foreach (StaticContainer::get('featureflag.storages') as $storage) {
            $storage->disableFeatureFlag($featureFlag);
        }

        return self::SUCCESS;
    }
}

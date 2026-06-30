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
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\FeatureFlags\ForcedFeatureFlagStateInterface;

class ListFeatureFlags extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('featureflags:list');
        $this->setDescription('List all available feature flags and their current state');
    }

    protected function doExecute(): int
    {
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);

        $rows = [];
        foreach (FeatureFlagFinder::findAll() as $featureFlag) {
            $state = $featureFlagManager->isFeatureActive(get_class($featureFlag)) ? 'enabled' : 'disabled';

            if ($featureFlag instanceof ForcedFeatureFlagStateInterface) {
                $state .= ' (forced)';
            }

            $rows[] = [$featureFlag->getName(), get_class($featureFlag), $state];
        }

        if (empty($rows)) {
            $this->getOutput()->writeln('No feature flags found.');
            return self::SUCCESS;
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp($a[0], $b[0]);
        });

        $this->renderTable(['Name', 'Class', 'State'], $rows);

        return self::SUCCESS;
    }
}

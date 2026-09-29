<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\BackgroundWarmer;

/**
 * marketplace:warm-cache console command
 */
class WarmCache extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('marketplace:warm-cache');
        $this->setDescription('Refetches the plugin and theme lists the Marketplace overview shows');
        $this->addRequiredValueOption(
            'if-older-than',
            null,
            'Only refetch when the lists are missing or at least this many seconds old',
            0
        );
    }

    protected function doExecute(): int
    {
        $ifOlderThan = (int) $this->getInput()->getOption('if-older-than');

        /** @var Client $client */
        $client = StaticContainer::get(Client::class);

        StaticContainer::get(BackgroundWarmer::class)->recordRun();

        // checked again here because a delayed run can start long after it was spawned, by which
        // time a visit may already have refreshed the lists
        $age = $client->getOverviewListsAge();

        if (null !== $age && $age < $ifOlderThan) {
            $this->getOutput()->writeln(sprintf('The lists are %d seconds old, nothing to do.', $age));

            return self::SUCCESS;
        }

        if (!$client->tryRefreshOverviewListCaches()) {
            $this->getOutput()->writeln('Some Marketplace lists could not be refreshed.');

            return self::FAILURE;
        }

        $this->getOutput()->writeln('Marketplace lists refreshed.');

        return self::SUCCESS;
    }
}

<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreUpdater\Updater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateMarketplacePlugins extends ConsoleCommand
{

    protected function configure()
    {
        $this->setName('marketplace:update-plugins');
        $this->setDescription('Migrates a measurable/website from one Matomo instance to another Matomo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = StaticContainer::get(Updater::class);
        $messages = $updater->oneClickUpdatePartTwo();

        foreach ($messages as $message) {
            $output->writeln($message);
        }
    }

}

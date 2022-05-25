<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\Plugins\Marketplace\API;

/**
 * marketplace:set-license-key console command
 */
class SetLicenseKey extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('marketplace:set-license-key');
        $this->setDescription('Sets a marketplace license key');
        $this->addOption('license-key', null, InputOption::VALUE_REQUIRED, 'Your license key:');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $licenseKey = $input->getOption('license-key');

        if (empty(trim($licenseKey))) {
            API::getInstance()->deleteLicenseKey();
            $output->writeln("License key removed.");
            return;
        }

        API::getInstance()->saveLicenseKey($licenseKey);
        $output->writeln("License key set.");
    }
}

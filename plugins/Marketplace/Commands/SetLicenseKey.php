<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Commands;

use Piwik\Plugin\ConsoleCommand;
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
        $this->addRequiredValueOption('license-key', null, 'Your license key:');
    }

    protected function doExecute(): int
    {
        $licenseKey = $this->getInput()->getOption('license-key');

        if (empty(trim($licenseKey))) {
            API::getInstance()->deleteLicenseKey();
            $this->getOutput()->writeln("License key removed.");
            return self::SUCCESS;
        }

        API::getInstance()->saveLicenseKey($licenseKey);
        $this->getOutput()->writeln("License key set.");

        return self::SUCCESS;
    }
}

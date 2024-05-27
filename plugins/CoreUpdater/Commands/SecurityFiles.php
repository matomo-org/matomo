<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Commands;

use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Plugin\ConsoleCommand;

/**
 * @package CoreUpdater
 */
class SecurityFiles extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:create-security-files');

        $this->setDescription('Creates some web server security files if they haven\'t existed previously. Useful when using for example Apache or IIS web server and Matomo cannot create these files automatically because of missing write permissions.');
    }

    protected function doExecute(): int
    {
        ServerFilesGenerator::createFilesForSecurity();
        $this->getOutput()->writeln('Done. To check if this worked please open the system report or run `./console diagnostics:run` and look out for the private directories check. If it doesn\'t work you may need to execute this command using a user that has write permissions or maybe you are not using Apache or IIS web server. Please note you may need to execute this command every time you update Matomo to a newer version.');

        return self::SUCCESS;
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;

/**
 */
class ClearCaches extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:clear-caches');
        $this->setAliases(array('cache:clear'));
        $this->setDescription('Clears all caches. This command can be useful for instance after updating Matomo files manually.');
    }

    /**
     * Execute command like: ./console core:clear-caches
     */
    protected function doExecute(): int
    {
        // Note: the logic for this command must be refactored in this helper function below.
        Filesystem::deleteAllCacheOnUpdate();

        $this->writeSuccessMessage('Caches cleared');

        return self::SUCCESS;
    }
}

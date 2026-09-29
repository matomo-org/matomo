<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Framework\Mock;

/**
 * Decides everything the real warmer does but never starts the process: one started from a test
 * would run outside the test environment, against the installation's own config and database.
 */
class BackgroundWarmer extends \Piwik\Plugins\Marketplace\BackgroundWarmer
{
    protected function execute(string $command): void
    {
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updates as PiwikUpdates;

class Updates_5_12_0_b1 extends PiwikUpdates
{
    /**
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater)
    {
        return array(
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
        Marketplace::setUniqueIdIfNotConfigured();
    }
}

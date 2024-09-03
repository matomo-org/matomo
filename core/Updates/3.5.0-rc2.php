<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tracker\Cache;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_3_5_0_rc2 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        // trigger salt for user if being created and stored in database
        PrivacyManager::getUserIdSalt();
        Cache::deleteTrackerCache();
    }
}

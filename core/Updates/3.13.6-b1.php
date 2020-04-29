<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins;

use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 3.13.6-b1.
 */
class Updates_3_13_6_b1 extends PiwikUpdates
{

    public function doUpdate(Updater $updater)
    {
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['enable_fingerprinting_limited_when_cookie_disabled'] = 0;
        $config->Tracker = $tracker;
        $config->forceSave();
    }
}

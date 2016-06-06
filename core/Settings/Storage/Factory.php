<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\Settings\Storage;
use Piwik\SettingsServer;
use Piwik\Tracker\SettingsStorage;

class Factory
{
    public static function make($pluginName)
    {
        if (SettingsServer::isTrackerApiRequest()) {
            $storage = new SettingsStorage($pluginName);
        } else {
            $storage = new Storage($pluginName);
        }

        return $storage;
    }
}

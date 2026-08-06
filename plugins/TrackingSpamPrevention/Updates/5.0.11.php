<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Config;
use Piwik\Settings\Storage\Factory;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_5_0_11 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $pluginConfig = Config::getInstance()->TrackingSpamPrevention;

        if (is_array($pluginConfig) && array_key_exists('block_headless', $pluginConfig)) {
            return;
        }

        $backend = (new Factory())->getPluginStorage('TrackingSpamPrevention', '')->getBackend();
        $currentValue = $backend->loadValue('block_headless', null);

        if ($currentValue !== null) {
            return;
        }

        $backend->saveValue('block_headless', false);
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 2.16.2-b2.
 */
class Updates_2_16_2_b2 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $pluginName = 'UserId';

        try {
            if (!$pluginManager->isPluginActivated($pluginName)) {
                $pluginManager->activatePlugin($pluginName);
            }
        } catch (\Exception $e) {
        }
    }
}

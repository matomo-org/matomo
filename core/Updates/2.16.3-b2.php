<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 2.16.3b2
 */
class Updates_2_16_3_b2 extends PiwikUpdates
{

    public function doUpdate(Updater $updater)
    {
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('CustomPiwikJs');
        } catch (\Exception $e) {
        }
    }
}

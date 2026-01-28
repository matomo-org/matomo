<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\CustomJsTracker\Exception\AccessDeniedException;

/**
 * API for plugin CustomJsTracker.
 *
 * Exposes whether plugin tracker JavaScript files are injected into the main
 * tracker automatically or must be loaded manually by the integrator.
 *
 * @method static \Piwik\Plugins\CustomJsTracker\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Reports whether plugin tracker JavaScript files are auto-included in piwik.js.
     *
     * @return bool True when plugin trackers are injected automatically; false when they must be loaded manually.
     */
    public function doesIncludePluginTrackersAutomatically()
    {
        Piwik::checkUserHasSomeAdminAccess();

        try {
            $updater = StaticContainer::get('Piwik\Plugins\CustomJsTracker\TrackerUpdater');
            $updater->checkWillSucceed();
            return true;
        } catch (AccessDeniedException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

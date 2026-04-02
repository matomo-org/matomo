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
 * Provides API methods for custom JavaScript tracker configuration.
 *
 * @method static \Piwik\Plugins\CustomJsTracker\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns whether plugin tracker files will be included automatically in `matomo.js`.
     *
     * @return bool Whether plugin tracker files are included automatically.
     */
    public function doesIncludePluginTrackersAutomatically(): bool
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

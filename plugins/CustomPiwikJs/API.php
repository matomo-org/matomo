<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\CustomPiwikJs\Exception\AccessDeniedException;

/**
 * API for plugin CustomPiwikJs
 *
 * @method static \Piwik\Plugins\CustomPiwikJs\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Detects whether plugin trackers will be automatically added to piwik.js or not. If not, the plugin tracker files
     * need to be loaded manually.
     * @return bool
     */
    public function doesIncludePluginTrackersAutomatically()
    {
        Piwik::checkUserHasSomeAdminAccess();

        try {
            $updater = StaticContainer::get('Piwik\Plugins\CustomPiwikJs\TrackerUpdater');
            $updater->checkWillSucceed();
            return true;
        } catch (AccessDeniedException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

}

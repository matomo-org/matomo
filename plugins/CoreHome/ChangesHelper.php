<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\UsersManager\Model AS UsersModel;

/**
 * CoreHome changes helper class
 */
class ChangesHelper
{

    /**
     * Return an array of changes provided by plugins
     *
     * @return array
     */
    public static function getChanges()
    {
        $showAtLeast = 20;
        $expireOlderThanDays = 180;

        // Load changes.json files for all plugins
        $manager = Manager::getInstance();
        $allChanges = [];
        $latestDate = '2020-01-01';
        foreach ($manager->getAllPluginsNames() as $pluginName) {
            $file = Manager::getPluginDirectory($pluginName).'/changes.json';
            if (file_exists($file)) {
                $changes = file_get_contents($file);
                if ($changes) {
                    $json = json_decode($changes, true);
                    if ($json && is_array($json)) {
                        foreach ($json as $change) {
                            if (isset($change['date'])) {
                                if ($change['date'] > $latestDate) {
                                    $latestDate = $change['date'];
                                }

                                $change['plugin'] = $pluginName;
                                $allChanges[] = $change;
                            }
                        }
                    }
                }
            }
        }
        // Sort all, recent first
        usort($allChanges, function ($a, $b) {
            return $a['date'] < $b['date'] ? 1 : -1;
        });

        // Remove expired changes, only if there are at more than the minimum changes
        $now = date("Y-m-d", time()-(86400*$expireOlderThanDays));
        if (count($allChanges) > $showAtLeast) {
            foreach ($allChanges as $k => $change) {
                if ($change['date'] < $now) {
                    unset($allChanges[$k]);
                }
            }
        }

        // Record the time that changes were viewed for the current user
        $login = Piwik::getCurrentUserLogin();
        $model = new UsersModel();
        $model->updateUserFields($login, array('ts_changes_viewed' => Date::now()->getDatetime()));
        return ['changes' => $allChanges, 'latestDate' => $latestDate];
    }

}

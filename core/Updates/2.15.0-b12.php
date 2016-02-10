<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Piwik\Config;
use Piwik\UpdateCheck;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_15_0_b12 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $this->migrateBetaUpgradesToReleaseChannel();
    }

    private function migrateBetaUpgradesToReleaseChannel()
    {
        $config = Config::getInstance();
        $debug  = $config->Debug;

        if (array_key_exists('allow_upgrades_to_beta', $debug)) {
            $allowUpgradesToBeta = 1 == $debug['allow_upgrades_to_beta'];
            unset($debug['allow_upgrades_to_beta']);

            $general = $config->General;
            if ($allowUpgradesToBeta) {
                $general['release_channel'] = 'latest_beta';
            } else {
                $general['release_channel'] = 'latest_stable';
            }

            $config->Debug   = $debug;
            $config->General = $general;
            $config->forceSave();
        }
    }
}

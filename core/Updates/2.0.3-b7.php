<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Filesystem;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Updates;

/**
 */
class Updates_2_0_3_b7 extends Updates
{
    static function update()
    {
        $errors = array();

        try {
            // enable DoNotTrack check in PrivacyManager if DoNotTrack plugin was enabled
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('DoNotTrack')) {
                DoNotTrackHeaderChecker::activate();
            }

            // enable IP anonymization if AnonymizeIP plugin was enabled
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('AnonymizeIP')) {
                IPAnonymizer::activate();
            }
        } catch (\Exception $ex) {
            // pass
        }

        // disable & delete old plugins
        $oldPlugins = array('DoNotTrack', 'AnonymizeIP');
        foreach ($oldPlugins as $plugin) {
            try {
                \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($plugin);
            } catch(\Exception $e) {

            }

            $dir = PIWIK_INCLUDE_PATH . "/plugins/$plugin";

            if (file_exists($dir)) {
                Filesystem::unlinkRecursive($dir, true);
            }

            if (file_exists($dir)) {
                $errors[] = "Please delete this directory manually (eg. using your FTP software): $dir \n";
            }

        }
        if (!empty($errors)) {
            throw new \Exception("Warnings during the update: <br>" . implode("<br>", $errors));
        }
    }
}

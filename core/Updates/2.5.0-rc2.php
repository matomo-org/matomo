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
use Piwik\Updates;

/**
 * Update for version 2.5.0-rc2.
 */
class Updates_2_5_0_rc2 extends Updates
{

    static function update()
    {
        $files = self::getFilesToDeleteIfOld();

        foreach ($files as $file) {
            $path = PIWIK_INCLUDE_PATH . $file;

            if (file_exists($path)) {
                if (function_exists('opcache_invalidate')) {
                    @opcache_invalidate($file, $force = true);
                }
                self::deleteIfLastModifiedBefore14August2014($path);
            }
        }
    }

    private static function deleteIfLastModifiedBefore14August2014($path)
    {
        $modifiedTime = filemtime($path);

        if ($modifiedTime && $modifiedTime < 1408000000) {
            Filesystem::deleteFileIfExists($path);
        }
    }

    private static function getFilesToDeleteIfOld()
    {
        return array(
            '/misc/others/test_cookies_GenerateHundredsWebsitesAndVisits.php',
            '/misc/others/test_generateLotsVisitsWebsites.php',
            '/core/Tracker/ActionEvent.php',
            '/plugins/Actions/Widgets.php',
            '/plugins/CustomVariables/Menu.php',
            '/plugins/CustomVariables/Widgets.php',
            '/plugins/DevicesDetection/Widgets.php',
            '/plugins/Events/Widgets.php',
            '/plugins/ExampleRssWidget/Controller.php',
            '/plugins/Live/Menu.php',
            '/plugins/Provider/Widgets.php',
            '/plugins/SEO/Controller.php',
            '/plugins/UserCountry/Widgets.php',
            '/plugins/UserSettings/Widgets.php',
            '/plugins/VisitTime/Widgets.php',
            '/plugins/VisitorInterest/Widgets.php',
            '/plugins/CoreVisualizations/Visualizations/HtmlTable/Goals.php'
        );
    }
}

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
 */
class Updates_2_0_b13 extends Updates
{
    public static function update()
    {
        $errors = array();

        // Deleting old libs
        $obsoleteDirectories = array(
            PIWIK_INCLUDE_PATH . '/libs/Smarty',
            PIWIK_INCLUDE_PATH . '/libs/Event',
        );

        foreach ($obsoleteDirectories as $dir) {
            if (file_exists($dir)) {
                Filesystem::unlinkRecursive($dir, true);
            }

            if (file_exists($dir)) {
                $errors[] = "Please delete this directory manually (eg. using your FTP software): $dir \n";
            }

        }
        if(!empty($errors)) {
            throw new \Exception("Warnings during the update: <br>" . implode("<br>", $errors));
        }
   }
}

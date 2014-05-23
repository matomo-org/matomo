<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Faker\Provider\File;
use Piwik\Filesystem;
use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Updates;

/**
 */
class Updates_2_2_3_b7 extends Updates
{
    public static function update()
    {
        // Delete all existing htaccess files
        $files = Filesystem::globr( PIWIK_INCLUDE_PATH, ".htaccess");

        foreach($files as $file) {
            @unlink($file);
        }

        // Re-create them
        ServerFilesGenerator::createHtAccessFiles();
    }
}

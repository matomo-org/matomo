<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updates;

use Piwik\Db;
use Piwik\Updates;

/**
 */
class Updates_2_2_0_b15 extends Updates
{
    static function update()
    {
        // This was added in the beta cycle and then removed
        // if the file is there, it can cause bugs (we don't have an archiver in VisitFrequency anymore)
        $path = PIWIK_INCLUDE_PATH . '/plugins/VisitFrequency/Archiver.php';
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

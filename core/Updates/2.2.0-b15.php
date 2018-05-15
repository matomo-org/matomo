<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updates;

use Piwik\Updates;
use Piwik\Updater;

/**
 */
class Updates_2_2_0_b15 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        // This was added in the beta cycle and then removed
        // if the file is there, it can cause bugs (we don't have an archiver in VisitFrequency anymore)
        $path = PIWIK_INCLUDE_PATH . '/plugins/VisitFrequency/Archiver.php';
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

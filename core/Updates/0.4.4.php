<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Piwik;
use Piwik\Updates;
use Piwik\Updater;

/**
 */
class Updates_0_4_4 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $obsoleteFile = PIWIK_DOCUMENT_ROOT . '/libs/open-flash-chart/php-ofc-library/ofc_upload_image.php';
        if (file_exists($obsoleteFile)) {
            $rc = @unlink($obsoleteFile);
            if (!$rc) {
                throw new \Exception(Piwik::translate('General_ExceptionUndeletableFile', array($obsoleteFile)));
            }
        }
    }
}

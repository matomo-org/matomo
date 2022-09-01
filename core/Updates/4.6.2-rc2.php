<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Filesystem;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 4.6.2-rc2.
 */
class Updates_4_6_2_rc2 extends PiwikUpdates
{
    /**
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/misc/composer', true);
        @unlink(PIWIK_INCLUDE_PATH . '/node_modules/iframe-resizer/.eslintrc');
        @unlink(PIWIK_INCLUDE_PATH . '/node_modules/jquery.dotdotdot/.npmignore');
        @unlink(PIWIK_INCLUDE_PATH . '/node_modules/ng-dialog/.eslintrc');
    }
}

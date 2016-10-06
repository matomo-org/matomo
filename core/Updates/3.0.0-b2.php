<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Db;
use Piwik\Plugins\Dashboard;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updates;

class Updates_3_0_0_b2 extends Updates
{
    public static function isMajorUpdate()
    {
        return true;
    }
}

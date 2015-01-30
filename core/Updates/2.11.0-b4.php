<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updates;

use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\Utility\DuplicateActionRemover;
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 2.11.0-b4.
 */
class Updates_2_11_0_b4 extends Updates
{
    static function getSql()
    {
        $remover = new DuplicateActionRemover();
        return $remover->getSqlToRemoveDuplicateActions();
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class Visit extends LogTable
{
    public function getName()
    {
        return 'log_visit';
    }

    public function getColumnToJoinOnIdVisit()
    {
        return 'idvisit';
    }

    public function shouldJoinWithSubSelect()
    {
        return true;
    }

}
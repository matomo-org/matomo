<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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

    public function getIdColumn()
    {
        return 'idvisit';
    }

    public function getColumnToJoinOnIdVisit()
    {
        return 'idvisit';
    }
    
    public function getDateTimeColumn()
    {
        return 'visit_last_action_time';
    }

    public function shouldJoinWithSubSelect()
    {
        return true;
    }

    public function getPrimaryKey()
    {
        return array('idvisit');
    }
}
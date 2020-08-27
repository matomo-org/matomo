<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class Action extends LogTable
{
    public function getName()
    {
        return 'log_action';
    }

    public function getIdColumn()
    {
        return 'idaction';
    }

    public function getColumnToJoinOnIdAction()
    {
        return 'idaction';
    }

    public function getLinkTableToBeAbleToJoinOnVisit()
    {
        return 'log_link_visit_action';
    }

    public function getPrimaryKey()
    {
        return array('idaction');
    }
}

<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class LinkVisitAction extends LogTable
{
    public function getName()
    {
        return 'log_link_visit_action';
    }

    public function getIdColumn()
    {
        return 'idlink_va';
    }

    public function getColumnToJoinOnIdAction()
    {
        return 'idaction_url';
    }

    public function getColumnToJoinOnIdVisit()
    {
        return 'idvisit';
    }

    public function getDateTimeColumn()
    {
        return 'server_time';
    }

    public function getPrimaryKey()
    {
        return array('idlink_va');
    }
}

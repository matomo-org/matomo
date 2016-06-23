<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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

    public function getColumnToJoinOnIdAction()
    {
        return 'idaction_url';
    }

    public function getColumnToJoinOnIdVisit()
    {
        return 'idvisit';
    }
}

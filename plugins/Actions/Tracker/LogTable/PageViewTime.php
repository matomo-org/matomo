<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class PageViewTime extends LogTable
{
    public function getName()
    {
        return 'log_page_view_time';
    }

    public function getIdColumn()
    {
        return 'idpageviewtime';
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
        return ['idpageviewtime'];
    }

    public function hasIdVisitorColumn(): bool
    {
        return true;
    }
}

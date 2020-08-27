<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Tracker\LogTable;

use Piwik\Tracker\LogTable;

class Conversion extends LogTable
{
    public function getName()
    {
        return 'log_conversion';
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
        return 'server_time';
    }

    public function getPrimaryKey()
    {
        return array('idvisit', 'idgoal', 'buster');
    }
}
<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker;

class ServerTime extends ActionDimension
{
    protected $columnName = 'server_time';
    protected $columnType = 'DATETIME NOT NULL';

    public function install($actionColumns)
    {
        if (array_key_exists($this->columnName, $actionColumns)) {
            return array();
        }

        return array(
            Common::prefixTable("log_link_visit_action") => array(
                "ADD COLUMN server_time DATETIME NOT NULL",
                "ADD INDEX index_idsite_servertime ( idsite, server_time )"
            )
        );
    }

    public function getName()
    {
        return '';
    }

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        $timestamp = $request->getCurrentTimestamp();

        return Tracker::getDatetimeFromTimestamp($timestamp);
    }
}

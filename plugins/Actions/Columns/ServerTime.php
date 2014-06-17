<?php
/**
 * Piwik - Open source web analytics
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
    protected $fieldName = 'server_time';
    protected $fieldType = 'DATETIME NOT NULL';

    public function install()
    {
        parent::install();

        $sql = "ALTER TABLE `" . Common::prefixTable("log_link_visit_action") . "` ADD INDEX index_idsite_servertime ( idsite, server_time )";
        Db::exec($sql);
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

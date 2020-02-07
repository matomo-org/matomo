<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Dao;

use Piwik\Common;

class DataFinder
{

    public function hasTrackedData()
    {
        $sql = sprintf('SELECT idsite FROM %s LIMIT 1', Common::prefixTable('log_visit'));

        $result = \Piwik\Db::fetchOne($sql, array());

        return !empty($result);
    }

    public function hasAddedWebsite($login)
    {
        $sql = sprintf("SELECT count(*) as num_websites FROM %s WHERE idsite != 1 and creator_login = ? LIMIT 1", Common::prefixTable('site'));

        $result = \Piwik\Db::fetchOne($sql, array($login));

        return $result > 0;
    }

    public function hasAddedNewEmailReport($login)
    {
        $sql = sprintf("SELECT count(*) as num_reports FROM %s WHERE login = ? LIMIT 1", Common::prefixTable('report'));

        $result = \Piwik\Db::fetchOne($sql, array($login));

        return $result > 0;
    }

    public function hasAddedOrCustomisedDashboard($login)
    {
        $sql = sprintf("SELECT count(*) as num_dashboards FROM %s WHERE login = ? LIMIT 1", Common::prefixTable('user_dashboard'));

        $result = \Piwik\Db::fetchOne($sql, array($login));

        return $result > 0;
    }

    public function hasAddedSegment($login)
    {
        $sql = sprintf("SELECT count(*) as num_segments FROM %s WHERE login = ? LIMIT 1", Common::prefixTable('segment'));

        $result = \Piwik\Db::fetchOne($sql, array($login));

        return $result > 0;
    }

}
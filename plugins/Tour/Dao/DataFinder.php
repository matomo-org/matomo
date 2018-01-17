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
        $sql = sprintf('SELECT idsite FROM %s limit 1', Common::prefixTable('log_visit'));

        $result = \Piwik\Db::fetchOne($sql, array());

        return !empty($result);
    }

    public function hasCreatedGoal()
    {
        $sql = sprintf('SELECT idsite FROM %s limit 1', Common::prefixTable('goal'));

        $result = \Piwik\Db::fetchOne($sql, array());

        return !empty($result);
    }

    public function hasAddedUser()
    {
        $sql = sprintf("SELECT count(*) as num_users FROM %s WHERE login != 'anonymous'", Common::prefixTable('user'));

        $result = \Piwik\Db::fetchOne($sql, array());

        return $result > 1;
    }

    public function hasAddedWebsite()
    {
        $sql = sprintf("SELECT count(*) as num_websites FROM %s", Common::prefixTable('site'));

        $result = \Piwik\Db::fetchOne($sql, array());

        return $result > 1;
    }

}
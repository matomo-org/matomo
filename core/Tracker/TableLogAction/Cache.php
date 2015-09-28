<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker\TableLogAction;

use Piwik\Common;
use Piwik\Config;

class Cache
{
    public $enable;
    protected $lifetime;

    public function __construct()
    {
        $this->enable = Config::getInstance()->General['enable_segments_subquery_cache'];
        $this->lifetime = 60 * 10;
    }

    /**
     * @param $valueToMatch
     * @param $sql
     * @return array|null
     * @throws \Exception
     */
    public function getIdActionFromSegment($valueToMatch, $sql)
    {
        if (!$this->enable) {
            return array(
                // mark that the returned value is an sql-expression instead of a literal value
                'SQL' => $sql,
                'bind' => $valueToMatch,
            );
        }

        $ids = self::getIdsFromCache($valueToMatch, $sql);

        if(is_null($ids)) {
            return $ids;
        }

        $sql = Common::getSqlStringFieldsArray($ids);
        $bind = $ids;

        return array(
            // mark that the returned value is an sql-expression instead of a literal value
            'SQL'  => $sql,
            'bind' => $bind,
        );
    }


    /**
     * @param $valueToMatch
     * @param $sql
     * @return array|bool|float|int|string
     */
    private function getIdsFromCache($valueToMatch, $sql)
    {
        $cache = \Piwik\Cache::getLazyCache();

        $cacheKey = $this->getCacheKey($valueToMatch, $sql);

        if ($cache->contains($cacheKey) === true) {
            return $cache->fetch($cacheKey);
        }

        $ids = $this->fetchIdsFromDb($valueToMatch, $sql);

        $cache->save($cacheKey, $ids, $this->lifetime);

        return $ids;
    }

    /**
     * @param $valueToMatch
     * @param $sql
     * @return string
     * @throws
     */
    private function getCacheKey($valueToMatch, $sql)
    {
        if(is_array($valueToMatch)) {
            throw new \Exception("value to match is an array: this is not expected");
        }

        $uniqueKey = md5($sql . $valueToMatch);
        $cacheKey = 'TableLogAction.getIdActionFromSegment.' . $uniqueKey;
        return $cacheKey;
    }

    /**
     * @param $valueToMatch
     * @param $sql
     * @return array|null
     * @throws \Exception
     */
    private function fetchIdsFromDb($valueToMatch, $sql)
    {
        $idActions = \Piwik\Db::fetchAll($sql, $valueToMatch);

        $ids = array();
        foreach ($idActions as $idAction) {
            $ids[] = $idAction['idaction'];
        }

        if (!empty($ids)) {
            return $ids;
        }

        // no action was found for CONTAINS / DOES NOT CONTAIN
        return null;
    }
}
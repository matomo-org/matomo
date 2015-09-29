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
use Piwik\Container\StaticContainer;
use Psr\Log\LoggerInterface;

class Cache
{
    /**
     * @var bool
     */
    public $isEnabled;

    /**
     * @var int cache lifetime in seconds
     */
    protected $lifetime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * for tests
     *
     * @var int
     */
    static public $hits = 0;

    public function __construct()
    {
        $this->isEnabled = (bool)Config::getInstance()->General['enable_segments_subquery_cache'];
        $this->lifetime = 60 * 10;
        $this->logger = StaticContainer::get('Psr\Log\LoggerInterface');
    }

    /**
     * @param $valueToMatch
     * @param $sql
     * @return array|null
     * @throws \Exception
     */
    public function getIdActionFromSegment($valueToMatch, $sql)
    {
        if (!$this->isEnabled) {
            return array(
                // mark that the returned value is an sql-expression instead of a literal value
                'SQL' => $sql,
                'bind' => $valueToMatch,
            );
        }

        $ids = self::getIdsFromCache($valueToMatch, $sql);

        if(count($ids) == 0) {
            return null;
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
        $cache = $this->buildCache();
        $cacheKey = $this->getCacheKey($valueToMatch, $sql);

        if ($cache->contains($cacheKey) === true) {
            self::$hits++;
            $this->logger->debug("Segment subquery cache HIT (for '$valueToMatch' and SQL '$sql)");
            return $cache->fetch($cacheKey);
        }

        $ids = $this->fetchActionIdsFromDb($valueToMatch, $sql);

        $cache->save($cacheKey, $ids, $this->lifetime);
        $this->logger->debug("Segment subquery cache SAVE (for '$valueToMatch' and SQL '$sql')'");

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
    private function fetchActionIdsFromDb($valueToMatch, $sql)
    {
        $idActions = \Piwik\Db::fetchAll($sql, $valueToMatch);

        $ids = array();
        foreach ($idActions as $idAction) {
            $ids[] = $idAction['idaction'];
        }

        return $ids;
    }

    /**
     * @return \Piwik\Cache\Lazy
     */
    private function buildCache()
    {
        return \Piwik\Cache::getLazyCache();
    }
}
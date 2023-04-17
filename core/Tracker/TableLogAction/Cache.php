<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker\TableLogAction;

use Piwik\Common;
use Piwik\Config;
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
     * @var \Matomo\Cache\Lazy
     */
    private $cache;

    /**
     * @var bool
     */
    private $limitActionIds;

    public function __construct(LoggerInterface $logger, Config $config, \Matomo\Cache\Lazy $cache)
    {
        $this->isEnabled = (bool)$config->General['enable_segments_subquery_cache'];
        $this->limitActionIds = $config->General['segments_subquery_cache_limit'];
        $this->lifetime = $config->General['segments_subquery_cache_ttl'];
        $this->logger = $logger;
        $this->cache = $cache;
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

        if(is_null($ids)) {
            // Too Big To Cache, issue SQL as subquery instead
            return array(
                'SQL' => $sql,
                'bind' => $valueToMatch,
            );
        }

        if(count($ids) === 0) {
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
     * @return array of IDs, or null if the returnset is too big to cache
     */
    private function getIdsFromCache($valueToMatch, $sql)
    {
        $cacheKey = $this->getCacheKey($valueToMatch, $sql);

        if ($this->cache->contains($cacheKey) === true) { // TODO: hits
            $this->logger->debug("Segment subquery cache HIT (for '$valueToMatch' and SQL '$sql)");
            return $this->cache->fetch($cacheKey);
        }

        $ids = $this->fetchActionIdsFromDb($valueToMatch, $sql);

        if($this->isTooBigToCache($ids)) {
            $this->logger->debug("Segment subquery cache SKIPPED SAVE (too many IDs returned by subquery: %s ids)'", array(count($ids)));
            $this->cache->save($cacheKey, $ids = null, $this->lifetime);
            return null;
        }

        $this->cache->save($cacheKey, $ids, $this->lifetime);
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
        $idActions = \Piwik\Db::getReader()->fetchAll($sql, $valueToMatch);

        $ids = array();
        foreach ($idActions as $idAction) {
            $ids[] = $idAction['idaction'];
        }

        return $ids;
    }

    /**
     * @param $ids
     * @return bool
     */
    private function isTooBigToCache($ids)
    {
        return count($ids) > $this->limitActionIds;
    }
}

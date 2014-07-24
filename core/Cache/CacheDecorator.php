<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

use Piwik\Tracker;
use Piwik\Translate;

/**
 * Caching class used for static caching.
 */
class CacheDecorator implements CacheInterface
{
    /**
     * @var StaticCache
     */
    protected $staticCache;

    public function __construct(CacheInterface $cache)
    {
        $this->staticCache = $cache;
    }

    public function get()
    {
        return $this->staticCache->get();
    }

    public function has()
    {
        return $this->staticCache->has();
    }

    public function setCacheKey($cacheKey)
    {
        $this->staticCache->setCacheKey($cacheKey);
    }

    public function getCacheKey()
    {
        return $this->staticCache->getCacheKey();
    }

    public function set($content)
    {
        $this->staticCache->set($content);
    }

}

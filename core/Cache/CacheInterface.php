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
interface CacheInterface
{
    public function get();

    public function has();

    public function setCacheKey($cacheKey);

    public function getCacheKey();

    public function set($content);

}

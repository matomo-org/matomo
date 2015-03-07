<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Cache\Backend;
use Piwik\Container\StaticContainer;

class Cache
{

    /**
     * This can be considered as the default cache to use in case you don't know which one to pick. It does not support
     * the caching of any objects though. Only boolean, numbers, strings and arrays are supported. Whenever you request
     * an entry from the cache it will fetch the entry. Cache entries might be persisted but not necessarily. It
     * depends on the configured backend.
     *
     * @return Cache\Lazy
     */
    public static function getLazyCache()
    {
        return StaticContainer::get('Piwik\Cache\Lazy');
    }

    /**
     * This class is used to cache any data during one request. It won't be persisted between requests and it can
     * cache all kind of data, even objects or resources. This cache is very fast.
     *
     * @return Cache\Transient
     */
    public static function getTransientCache()
    {
        return StaticContainer::get('Piwik\Cache\Transient');
    }

    /**
     * This cache stores all its cache entries under one "cache" entry in a configurable backend.
     *
     * This comes handy for things that you need very often, nearly in every request. For example plugin metadata, the
     * list of tracker plugins, the list of available languages, ...
     * Instead of having to read eg. a hundred cache entries from files (or any other backend) it only loads one cache
     * entry which contains the hundred keys. Should be used only for things that you need very often and only for
     * cache entries that are not too large to keep loading and parsing the single cache entry fast.
     * All cache entries it contains have the same life time. For fast performance it won't validate any cache ids.
     * It is not possible to cache any objects using this cache.
     *
     * @return Cache\Eager
     */
    public static function getEagerCache()
    {
        return StaticContainer::get('Piwik\Cache\Eager');
    }

    public static function flushAll()
    {
        self::getLazyCache()->flushAll();
        self::getTransientCache()->flushAll();
        self::getEagerCache()->flushAll();
    }

    /**
     * @param $type
     * @return Cache\Backend
     */
    public static function buildBackend($type)
    {
        $factory = new Cache\Backend\Factory();
        $options = self::getOptions($type);

        $backend = $factory->buildBackend($type, $options);

        return $backend;
    }

    private static function getOptions($type)
    {
        $options = self::getBackendOptions($type);

        switch ($type) {
            case 'file':

                $options = array('directory' => StaticContainer::get('path.cache'));
                break;

            case 'chained':

                foreach ($options['backends'] as $backend) {
                    $options[$backend] = self::getOptions($backend);
                }

                break;

            case 'redis':

                if (!empty($options['timeout'])) {
                    $options['timeout'] = (float)Common::forceDotAsSeparatorForDecimalPoint($options['timeout']);
                }

                break;
        }

        return $options;
    }

    private static function getBackendOptions($backend)
    {
        $key = ucfirst($backend) . 'Cache';
        $options = Config::getInstance()->$key;

        return $options;
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

use Doctrine\Common\Cache\ApcCache;
use Piwik\Cache\Cache;
use Piwik\Common;

/**
 * Factory for the cache used to store static information (ie, DI definitions). Cache instances are
 * only created once, and by default, should be shared w/ caches used for dynamic data (ie, ones
 * accessed in DI), if the same backend is used.
 *
 * This class is a kernel global.
 */
class StaticCacheFactory
{
    /**
     * @var GlobalSettingsProvider
     */
    private $globalSettingsProvider;

    /**
     * @var Cache
     */
    private $redisCache = null;

    /**
     * @var Cache
     */
    private $apcCache = null;

    public function __construct(GlobalSettingsProvider $globalSettingsProvider)
    {
        $this->globalSettingsProvider = $globalSettingsProvider;
    }

    /**
     * @param string $backendType
     * @return \Doctrine\Common\Cache\Cache
     */
    public function make($backendType)
    {
        switch ($backendType) {
            case 'redis':
                return $this->buildRedisCache();
            case 'apc':
                return $this->buildApcCache();
            default:
                throw new \InvalidArgumentException("Invalid 'static' cache backend '$backendType'.");
        }
    }

    private function buildRedisCache()
    {
        if ($this->redisCache === null) {
            $redisConfig = $this->globalSettingsProvider->getSection('RedisCache');

            if (!empty($options['timeout'])) {
                $redisConfig['timeout'] = (float)Common::forceDotAsSeparatorForDecimalPoint($options['timeout']);
            }

            $cacheFactory = new \Piwik\Cache\Backend\Factory();
            $this->redisCache = $cacheFactory->buildRedisCache($redisConfig);
        }

        return $this->redisCache;
    }

    private function buildApcCache()
    {
        if ($this->apcCache === null) {
            if (!function_exists('apc_fetch')) {
                throw new \RuntimeException("Trying to create ApcCache, but cannot find APC functions like 'apc_fetch'!");
            }

            $this->apcCache = new ApcCache();
        }

        return $this->apcCache;
    }
}

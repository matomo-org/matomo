<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

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

    public function __construct(GlobalSettingsProvider $globalSettingsProvider)
    {
        $this->globalSettingsProvider = $globalSettingsProvider;
    }

    public function make($backendType)
    {
        switch ($backendType) {
            case 'redis':
                return $this->buildRedisCache();
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
}

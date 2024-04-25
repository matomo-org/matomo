<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Cache;

/**
 * @group Cache
 */
class CacheTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLazyCacheShouldCreateAnInstanceOfLazy()
    {
        $cache = Cache::getLazyCache();

        $this->assertTrue($cache instanceof \Matomo\Cache\Lazy);
    }

    public function testGetLazyCacheShouldAlwaysReturnTheSameInstance()
    {
        $cache1 = Cache::getLazyCache();
        $cache2 = Cache::getLazyCache();

        $this->assertSame($cache1, $cache2);
    }

    public function testGetEagerCacheShouldCreateAnInstanceOfEager()
    {
        $cache = Cache::getEagerCache();

        $this->assertTrue($cache instanceof \Matomo\Cache\Eager);
    }

    public function testGetEagerCacheShouldAlwaysReturnTheSameInstance()
    {
        $cache1 = Cache::getEagerCache();
        $cache2 = Cache::getEagerCache();

        $this->assertSame($cache1, $cache2);
    }

    public function testGetTransientCacheShouldCreateAnInstanceOfTransient()
    {
        $cache = Cache::getTransientCache();

        $this->assertTrue($cache instanceof \Matomo\Cache\Transient);
    }

    public function testGetTransientCacheShouldAlwaysReturnTheSameInstance()
    {
        $cache1 = Cache::getTransientCache();
        $cache2 = Cache::getTransientCache();

        $this->assertSame($cache1, $cache2);
    }

    public function testFlushAllShouldActuallyFlushAllCaches()
    {
        $cache1 = Cache::getTransientCache();
        $cache2 = Cache::getLazyCache();
        $cache3 = Cache::getEagerCache();

        $cache1->save('test1', 'content');
        $cache2->save('test2', 'content');
        $cache3->save('test3', 'content');

        $this->assertTrue($cache1->contains('test1'));
        $this->assertTrue($cache2->contains('test2'));
        $this->assertTrue($cache3->contains('test3'));

        Cache::flushAll();

        $this->assertFalse($cache1->contains('test1'));
        $this->assertFalse($cache2->contains('test2'));
        $this->assertFalse($cache3->contains('test3'));
    }
}

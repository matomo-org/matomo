<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Cache;

/**
 * @group Cache
 */
class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function test_getLazyCache_shouldCreateAnInstanceOfLazy()
    {
        $cache = Cache::getLazyCache();

        $this->assertTrue($cache instanceof Cache\Lazy);
    }

    public function test_getLazyCache_shouldAlwaysReturnTheSameInstance()
    {
        $cache1 = Cache::getLazyCache();
        $cache2 = Cache::getLazyCache();

        $this->assertSame($cache1, $cache2);
    }

    public function test_getEagerCache_shouldCreateAnInstanceOfEager()
    {
        $cache = Cache::getEagerCache();

        $this->assertTrue($cache instanceof Cache\Eager);
    }

    public function test_getEagerCache_shouldAlwaysReturnTheSameInstance()
    {
        $cache1 = Cache::getEagerCache();
        $cache2 = Cache::getEagerCache();

        $this->assertSame($cache1, $cache2);
    }

    public function test_getTransientCache_shouldCreateAnInstanceOfTransient()
    {
        $cache = Cache::getTransientCache();

        $this->assertTrue($cache instanceof Cache\Transient);
    }

    public function test_getTransientCache_shouldAlwaysReturnTheSameInstance()
    {
        $cache1 = Cache::getTransientCache();
        $cache2 = Cache::getTransientCache();

        $this->assertSame($cache1, $cache2);
    }

    public function test_flushAll_shouldActuallyFlushAllCaches()
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

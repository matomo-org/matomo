<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace PHPUnit\Unit\Tracker;

use Matomo\Cache\Lazy;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\Cache;

class CacheTest extends UnitTestCase
{
    private $methodsCalled;

    public function setUp(): void
    {
        parent::setUp();
        $this->methodsCalled = [];
    }

    public function test_withDelegatedCacheClears_onlyCallsCacheClearsAtTheEndOnce()
    {
        $this->assertEmpty($this->methodsCalled);
        Cache::withDelegatedCacheClears(function () {
            Cache::clearCacheGeneral();
            Cache::deleteTrackerCache();
            Cache::clearCacheGeneral();
            Cache::deleteCacheWebsiteAttributes(1);
            Cache::clearCacheGeneral();
            Cache::deleteCacheWebsiteAttributes(1);
            Cache::deleteCacheWebsiteAttributes(1);
            Cache::deleteCacheWebsiteAttributes(2);
            Cache::deleteTrackerCache();
            Cache::deleteCacheWebsiteAttributes(2);
            Cache::deleteTrackerCache();

            $this->assertEmpty($this->methodsCalled);
        });

        $expectedCalls = [
            'delete.general',
            'flushAll',
            'delete.1',
            'delete.2',
        ];
        $this->assertEquals($expectedCalls, $this->methodsCalled);
    }

    public function provideContainerConfig()
    {
        $mockLazyCache = $this->getMockBuilder(Lazy::class)
            ->onlyMethods(['flushAll', 'fetch', 'save', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockLazyCache
            ->method('flushAll')->willReturnCallback(function () {
                $this->methodsCalled[] = 'flushAll';
            });
        $mockLazyCache->method('delete')->willReturnCallback(function ($key) {
                $this->methodsCalled[] = 'delete.' . $key;
        });
        $mockLazyCache->method('save')->willReturnCallback(function ($id, $data) {
            $this->methodsCalled[] = 'save.' . $id . $data;
        });
        $mockLazyCache->method('fetch')->willReturnCallback(function ($id) {
            $this->methodsCalled[] = 'fetch.' . $id;
        });

        return [
            Lazy::class => $mockLazyCache,
        ];
    }
}

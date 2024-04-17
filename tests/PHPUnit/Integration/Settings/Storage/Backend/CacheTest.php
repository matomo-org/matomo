<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage\Backend;

use Piwik\Settings\Storage\Backend\Cache;
use Piwik\Tests\Framework\Mock\Settings\FakeBackend;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Settings
 * @group Backend
 * @group Storage
 */
class CacheTest extends IntegrationTestCase
{
    /**
     * @var FakeBackend
     */
    private $backend;

    /**
     * @var Cache
     */
    private $cacheBackend;

    public function setUp(): void
    {
        parent::setUp();

        $this->backend = new FakeBackend('MySuperStorageKey');
        $this->cacheBackend = new Cache($this->backend);
    }

    public function test_getStorageId_shouldReturnStorageOfActualPlugin()
    {
        $this->assertSame('MySuperStorageKey', $this->cacheBackend->getStorageId());
    }

    public function test_load_ShouldActuallyLoadDataFromBackend()
    {
        $this->assertSame($this->backend->load(), $this->cacheBackend->load());
        $this->assertNotEmpty($this->cacheBackend->load());
    }

    public function test_load_ShouldCacheData()
    {
        $this->assertNotValueCached();

        $this->cacheBackend->load();

        $this->assertValueCached();
        $this->assertValueIsActuallyInCache();
    }

    public function test_delete_ShouldClearCacheAndData()
    {
        $this->cacheBackend->load();
        $this->assertValueCached();

        $this->cacheBackend->delete();

        $this->assertNotValueCached();
        $this->assertSame(array(), $this->cacheBackend->load());
        $this->assertSame(array(), $this->backend->load());
    }

    public function test_save_ShouldClearCacheAndUpdateData()
    {
        $this->cacheBackend->load();
        $this->assertValueCached();

        $value = array('new' => 'value');
        $this->cacheBackend->save($value);

        $this->assertNotValueCached();

        $this->assertSame($value, $this->cacheBackend->load());
        $this->assertSame($value, $this->backend->load());

        $this->assertValueCached();
        $this->assertValueIsActuallyInCache();
    }

    private function assertValueIsActuallyInCache()
    {
        $this->assertSame($this->backend->load(), $this->getCache()->fetch($this->getCacheKey()));
    }

    private function assertValueCached()
    {
        $this->assertTrue($this->getCache()->contains($this->getCacheKey()));
    }

    private function assertNotValueCached()
    {
        $this->assertFalse($this->getCache()->contains($this->getCacheKey()));
    }

    private function getCacheKey()
    {
        return $this->cacheBackend->getStorageId();
    }

    private function getCache()
    {
        return Cache::buildCache();
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Lazy;
use Piwik\Translation\Loader\LoaderCache;

/**
 * @group Translation
 */
class LoaderCacheTest extends \PHPUnit\Framework\TestCase
{
    public function test_shouldNotLoad_ifInCache()
    {
        $cache = $this->getMockBuilder('Matomo\Cache\Lazy')->disableOriginalConstructor()->getMock();
        $cache->expects($this->any())
            ->method('fetch')
            ->willReturn(array('translations!'));
        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $wrappedLoader->expects($this->never())
            ->method('load');

        $loader = new LoaderCache($wrappedLoader, $cache);
        $translations = $loader->load('en', array('foo'));

        $this->assertEquals(array('translations!'), $translations);
    }

    public function test_shouldLoad_ifNotInCache()
    {
        $cache = $this->getMockBuilder('Matomo\Cache\Lazy')->disableOriginalConstructor()->getMock();
        $cache->expects($this->any())
            ->method('fetch')
            ->willReturn(null);
        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $wrappedLoader->expects($this->once())
            ->method('load')
            ->with('en', array('foo'))
            ->willReturn(array('translations!'));

        $loader = new LoaderCache($wrappedLoader, $cache);
        $translations = $loader->load('en', array('foo'));

        $this->assertEquals(array('translations!'), $translations);
    }

    public function test_shouldReLoad_ifDifferentDirectories()
    {
        $cache = new Lazy(new ArrayCache());

        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $wrappedLoader->expects($this->exactly(2))
            ->method('load')
            ->willReturn(array('translations!'));

        $loader = new LoaderCache($wrappedLoader, $cache);

        // Should call the wrapped loader only once
        $loader->load('en', array('foo'));
        $loader->load('en', array('foo'));

        // Should call the wrapped loader a second time
        $loader->load('en', array('foo', 'bar'));
    }
}

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
use Piwik\Version;

/**
 * @group Translation
 */
class LoaderCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldNotLoadIfInCache()
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

    public function testShouldLoadIfNotInCache()
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

    public function testShouldReLoadIfDifferentDirectories()
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

    public function testShouldNotReuseTranslationsCachedByAnotherVersion()
    {
        $cache = new Lazy(new ArrayCache());
        $cache->save('Translations-en-' . sha1('5.0.0' . 'foo'), array('old translations'));
        $cache->save('Translations-en-' . sha1('foo'), array('old translations'));

        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $wrappedLoader->expects($this->once())
            ->method('load')
            ->willReturn(array('translations!'));

        $loader = new LoaderCache($wrappedLoader, $cache);

        $this->assertEquals(array('translations!'), $loader->load('en', array('foo')));
        $this->assertEquals(array('translations!'), $cache->fetch('Translations-en-' . sha1(Version::VERSION . 'foo')));
    }
}

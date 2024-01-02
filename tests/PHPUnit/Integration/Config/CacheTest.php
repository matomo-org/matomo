<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Config\Cache;

use Piwik\Config;
use Piwik\Config\Cache;
use Piwik\Config\IniFileChain;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;
use Piwik\Url;

/**
 * @group Core
 */
class CacheTest extends IntegrationTestCase
{
    /**
     * @var Cache
     */
    private $cache;

    private $testHost = 'analytics.test.matomo.org';

    private $originalHost = '';

    public function setUp(): void
    {
        unset($GLOBALS['ENABLE_CONFIG_PHP_CACHE']);
        $this->setTrustedHosts();
        $this->originalHost = Url::getHost(false);
        Url::setHost($this->testHost);
        $this->cache = new Cache();
        $this->cache->doDelete(IniFileChain::CONFIG_CACHE_KEY);
        parent::setUp();
    }

    private function setTrustedHosts()
    {
        Config::setSetting('General', 'trusted_hosts', array($this->testHost, 'foonot.exists'));
    }

    public function tearDown(): void
    {
        $this->setTrustedHosts();
        $this->cache->doDelete(IniFileChain::CONFIG_CACHE_KEY);
        Url::setHost($this->originalHost);
        parent::tearDown();
    }

    public function test_doFetch_noValueSaved_shouldReturnFalse()
    {
        $noValue = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertFalse($noValue);
    }

    /**
     * @dataProvider getRandmHosts
     */
    public function test_construct_failsWhenUsingRandomHost($host)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported host');

        Url::setHost($host);
        new Cache();
    }

    public function getRandmHosts()
    {
        return [
            ['foo..test'],
            ['foo\test'],
            ['']
        ];
    }

    public function test_doSave_doFetch_savesAndReadsData()
    {
        $value = array('mergedSettings' => 'foobar', 'settingsChain' => array('bar' => 'baz'));
        $this->cache->doSave(IniFileChain::CONFIG_CACHE_KEY, $value, 60);
        $this->assertEquals($value, $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY));

        // also works when creating new instance to ensure it's read from file
        $this->cache = new Cache();
        $this->assertEquals($value, $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY));
    }

    public function test_doDelete()
    {
        $value = array('mergedSettings' => 'foobar', 'settingsChain' => array('bar' => 'baz'));
        $this->cache->doSave(IniFileChain::CONFIG_CACHE_KEY, $value, 60);

        $this->setTrustedHosts();

        $this->assertEquals($value, $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY));

        $this->cache->doDelete(IniFileChain::CONFIG_CACHE_KEY);

        $this->assertFalse($this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY));

        $cache = new Cache();
        $this->assertFalse($cache->doFetch(IniFileChain::CONFIG_CACHE_KEY));
    }

    public function test_isValidHost()
    {
        $this->assertTrue($this->cache->isValidHost(array('General' => array('trusted_hosts' => array('foo.com', $this->testHost, 'bar.baz')))));
        $this->assertFalse($this->cache->isValidHost(array('General' => array('trusted_hosts' => array('foo.com', 'bar.baz')))));
        $this->assertFalse($this->cache->isValidHost(array('General' => array('trusted_hosts' => array()))));
        $this->assertFalse($this->cache->isValidHost(array()));
    }
}

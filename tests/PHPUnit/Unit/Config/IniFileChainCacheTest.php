<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\Config;

use Piwik\Config;
use Piwik\Config\Cache;
use Piwik\Config\IniFileChain;

class TestIniFileChain extends IniFileChain
{
    public $addHostInfo = '';

    public function __construct(array $defaultSettingsFiles = array(), $userSettingsFile = null, $addhostInfo = '')
    {
        $this->addHostInfo = $addhostInfo;
        parent::__construct($defaultSettingsFiles, $userSettingsFile);
    }

    protected function mergeFileSettings()
    {
        $settings = parent::mergeFileSettings();

        if (!empty($this->addHostInfo)) {
            $settings['General'] = ['trusted_hosts'=> [$this->addHostInfo]];
        }

        return $settings;
    }
}

/**
 * @group Core
 */
class IniFileChainCacheTest extends IniFileChainTest
{
    /**
     * @var Cache
     */
    private $cache;

    private $testHost = 'mytest.matomo.org';

    public function setUp()
    {
        $GLOBALS['ENABLE_CONFIG_PHP_CACHE'] = true;
        $_SERVER['HTTP_HOST'] = $this->testHost;
        $this->cache = new Cache();
        parent::setUp();
        $this->setTrustedHosts();
    }

    private function setTrustedHosts()
    {
        Config::setSetting('General', 'trusted_hosts', array($this->testHost, 'foonot.exists'));
    }

    public function tearDown()
    {
        $this->cache->doDelete(IniFileChain::CONFIG_CACHE_KEY);
        unset($GLOBALS['ENABLE_CONFIG_PHP_CACHE']);
        unset($_SERVER['HTTP_HOST']);
        parent::tearDown();
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function test_reload_shouldNotPopulateCacheWhenNoTrustedHostIsConfigured($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile);
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function test_reload_shouldNotPopulateCacheWhenTrustedHostIsNotValid($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile, 'foo.bar.com');
        $expected['General'] = array('trusted_hosts' => array('foo.bar.com'));
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function test_reload_shoulPopulateCacheWhenTrustedHostIsValid($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile, $this->testHost);
        $expected['General'] = array('trusted_hosts' => array($this->testHost));
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $settingsChain = $value['settingsChain'];
        unset($value['settingsChain']);
        $this->assertEquals(array('mergedSettings' => $expected), $value);

        $this->assertArraySubset($defaultSettingFiles, array_keys($settingsChain));
        $this->assertNotEmpty(array_keys($settingsChain));
    }
    
    /**
     * @dataProvider getMergingTestData
     */
    public function test_reload_canReadFromCache($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        $userSettingsFileCopy = dirname($userSettingsFile) . '/copy.' . basename($userSettingsFile);
        copy($userSettingsFile, $userSettingsFileCopy);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFileCopy, $this->testHost);
        $expected['General'] = array('trusted_hosts' => array($this->testHost));
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        // even though the passed config files don't exist it still returns the same result as it is fetched from
        // cache
        unlink($userSettingsFileCopy);
        $testChain = new TestIniFileChain(array('foo'), $userSettingsFileCopy);
        $this->assertEquals($expected, $testChain->getAll(), "'$testDescription' failed");
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function test_populateCache_DeleteCache($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $this->test_reload_shoulPopulateCacheWhenTrustedHostIsValid($testDescription, $defaultSettingFiles, $userSettingsFile, $expected);

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertNotEmpty($value);

        // dumping the cache should delete it

        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile);
        $fileChain->deleteConfigCache();

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);
    }
}
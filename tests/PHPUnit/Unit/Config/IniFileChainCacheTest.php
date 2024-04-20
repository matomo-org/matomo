<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Config;

use Piwik\Config;
use Piwik\Config\Cache;
use Piwik\Config\IniFileChain;

class TestIniFileChain extends IniFileChain
{
    public $addHostInfo = '';

    public function __construct(array $defaultSettingsFiles = [], $userSettingsFile = null, $addhostInfo = '')
    {
        $this->addHostInfo = $addhostInfo;
        parent::__construct($defaultSettingsFiles, $userSettingsFile);
    }

    protected function mergeFileSettings()
    {
        $settings = parent::mergeFileSettings();

        if (!empty($this->addHostInfo)) {
            $settings['General'] = ['trusted_hosts' => [$this->addHostInfo]];
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

    public function setUp(): void
    {
        $GLOBALS['ENABLE_CONFIG_PHP_CACHE'] = true;
        $_SERVER['HTTP_HOST'] = $this->testHost;
        $this->cache = new Cache();
        parent::setUp();
        $this->setTrustedHosts();
    }

    private function setTrustedHosts()
    {
        Config::setSetting('General', 'trusted_hosts', [$this->testHost, 'foonot.exists']);
    }

    public function tearDown(): void
    {
        $this->cache->doDelete(IniFileChain::CONFIG_CACHE_KEY);
        unset($GLOBALS['ENABLE_CONFIG_PHP_CACHE']);
        unset($_SERVER['HTTP_HOST']);
        parent::tearDown();
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function testReloadShouldNotPopulateCacheWhenNoTrustedHostIsConfigured($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
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
    public function testReloadShouldNotPopulateCacheWhenTrustedHostIsNotValid($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile, 'foo.bar.com');
        $expected['General'] = ['trusted_hosts' => ['foo.bar.com']];
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function testReloadShoulPopulateCacheWhenTrustedHostIsValid($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile, $this->testHost);
        $expected['General'] = ['trusted_hosts' => [$this->testHost]];
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $settingsChain = $value['settingsChain'];
        unset($value['settingsChain']);
        $this->assertEquals(['mergedSettings' => $expected], $value);

        foreach ($defaultSettingFiles as $defaultSettingFile) {
            self::assertTrue(array_key_exists($defaultSettingFile, $settingsChain));
        }

        $this->assertNotEmpty(array_keys($settingsChain));
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function testReloadCanReadFromCache($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);

        $userSettingsFileCopy = dirname($userSettingsFile) . '/copy.' . basename($userSettingsFile);
        copy($userSettingsFile, $userSettingsFileCopy);

        // reading the chain should populate the cache
        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFileCopy, $this->testHost);
        $expected['General'] = ['trusted_hosts' => [$this->testHost]];
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");

        // ensure it can be read only from cache
        $testChain = new TestIniFileChain(['foo'], $userSettingsFileCopy);
        $this->assertEquals($expected, $testChain->getAll(), "'$testDescription' failed");
        unlink($userSettingsFileCopy);
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function testPopulateCacheDeleteCache($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $this->testReloadShoulPopulateCacheWhenTrustedHostIsValid($testDescription, $defaultSettingFiles, $userSettingsFile, $expected);

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertNotEmpty($value);

        // dumping the cache should delete it

        $fileChain = new TestIniFileChain($defaultSettingFiles, $userSettingsFile);
        $fileChain->deleteConfigCache();

        $value = $this->cache->doFetch(IniFileChain::CONFIG_CACHE_KEY);
        $this->assertEquals(false, $value);
    }
}

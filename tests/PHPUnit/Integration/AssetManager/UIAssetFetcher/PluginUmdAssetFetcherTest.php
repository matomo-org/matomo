<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Integration\AssetManager\UIAssetFetcher;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAssetFetcher\Chunk;
use Piwik\AssetManager\UIAssetFetcher\PluginUmdAssetFetcher;
use Piwik\Filesystem;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

class PluginUmdAssetFetcherTest extends UnitTestCase
{
    const TEST_PLUGINS_DIR = __DIR__ . '/plugins';

    const TEST_PLUGIN_UMD_SIZES = [
        'NoPluginUmd' => null,
        'TestPlugin1' => 10,
        'TestPlugin2' => 1,
        'TestPlugin3' => 3,
        'TestPlugin4' => 1,
        'TestPlugin5' => 5,
    ];

    const TEST_PLUGIN_DEPENDENCIES = [
        'NoPluginUmd' => null,
        'TestPlugin1' => [],
        'TestPlugin2' => ['TestPlugin1'],
        'TestPlugin3' => ['TestPlugin1', 'TestPlugin2'],
        'TestPlugin4' => ['TestPlugin5'],
        'TestPlugin5' => ['TestPlugin1', 'TestPlugin3'],
    ];

    private $oldPluginDirsEnvVar;
    private $oldPluginDirsGlobal;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // setup plugin test directories
        Filesystem::unlinkRecursive(self::TEST_PLUGINS_DIR, true);
        foreach (array_keys(self::TEST_PLUGIN_UMD_SIZES) as $pluginName) {
            $pluginSize = self::TEST_PLUGIN_UMD_SIZES[$pluginName];
            $pluginDependencies = self::TEST_PLUGIN_DEPENDENCIES[$pluginName];

            $vueDir = self::TEST_PLUGINS_DIR . '/' . $pluginName . '/vue/dist';
            $vueSrcDir = self::TEST_PLUGINS_DIR . '/' . $pluginName . '/vue/src';

            Filesystem::mkdir($vueDir);
            Filesystem::mkdir($vueSrcDir);

            if ($pluginSize === null) {
                continue;
            }

            $umdDependencies = [
                "dependsOn" => $pluginDependencies,
            ];
            $umdDependenciesPath = $vueDir . '/umd.metadata.json';

            file_put_contents($umdDependenciesPath, json_encode($umdDependencies));

            $umdPath = $vueDir . '/' . $pluginName . '.umd.min.js';
            $umdContent = "// begin $pluginName\n";
            $umdContent .= str_repeat(".", $pluginSize * 1024);
            $umdContent .= "// end $pluginName\n";

            file_put_contents($umdPath, $umdContent);

            self::assertEquals($pluginSize, floor(filesize($umdPath) / 1024));
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        Filesystem::unlinkRecursive(self::TEST_PLUGINS_DIR, true);
    }

    public function setUp(): void
    {
        $this->oldPluginDirsEnvVar = getenv('MATOMO_PLUGIN_DIRS');
        $this->oldPluginDirsGlobal = $GLOBALS['MATOMO_PLUGIN_DIRS'];

        parent::setUp();

        clearstatcache(true);

        putenv("MATOMO_PLUGIN_DIRS=" . self::TEST_PLUGINS_DIR . ';'
            . str_replace(PIWIK_INCLUDE_PATH, '', self::TEST_PLUGINS_DIR));
        unset($GLOBALS['MATOMO_PLUGIN_DIRS']);
        Manager::initPluginDirectories();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        clearstatcache(true);

        putenv("MATOMO_PLUGIN_DIRS={$this->oldPluginDirsEnvVar}");
        $GLOBALS['MATOMO_PLUGIN_DIRS'] = $this->oldPluginDirsGlobal;
        Manager::initPluginDirectories();
    }

    public function test_getChunkFiles_whenLoadingUmdsIndividually()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, true);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk('TestPlugin1', [self::getUmdFile('TestPlugin1')]),
            new Chunk('TestPlugin2', [self::getUmdFile('TestPlugin2')]),
            new Chunk('TestPlugin3', [self::getUmdFile('TestPlugin3')]),
            new Chunk('TestPlugin5', [self::getUmdFile('TestPlugin5')]),
            new Chunk('TestPlugin4', [self::getUmdFile('TestPlugin4')]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function test_getChunkFiles_whenLoadingUmdsIndividually_andNotAllPluginsActivated()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        unset($plugins[array_search('TestPlugin5', $plugins)]);

        $instance = new PluginUmdAssetFetcher($plugins, null, null, true);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk('TestPlugin1', [self::getUmdFile('TestPlugin1')]),
            new Chunk('TestPlugin2', [self::getUmdFile('TestPlugin2')]),
            new Chunk('TestPlugin3', [self::getUmdFile('TestPlugin3')]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function test_getChunkFiles_whenOneChunkConfigured()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, false, 1);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk(0, [
                self::getUmdFile('TestPlugin1'),
                self::getUmdFile('TestPlugin2'),
                self::getUmdFile('TestPlugin3'),
                self::getUmdFile('TestPlugin5'),
                self::getUmdFile('TestPlugin4'),
            ]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function test_getChunkFiles_whenNothingConfigured()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, false, null);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk(0, [
                self::getUmdFile('TestPlugin1'),
            ]),
            new Chunk(1, [
                self::getUmdFile('TestPlugin2'),
                self::getUmdFile('TestPlugin3'),
            ]),
            new Chunk(2, [
                self::getUmdFile('TestPlugin5'),
                self::getUmdFile('TestPlugin4'),
            ]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function test_getChunkFiles_whenMultipleChunksConfigured()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, false, 2);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk(0, [
                self::getUmdFile('TestPlugin1'),
            ]),
            new Chunk(1, [
                self::getUmdFile('TestPlugin2'),
                self::getUmdFile('TestPlugin3'),
                self::getUmdFile('TestPlugin5'),
                self::getUmdFile('TestPlugin4'),
            ]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function test_getChunkFiles_whenMultipleChunksConfigured_andNotAllPluginsActivated()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        unset($plugins[array_search('TestPlugin5', $plugins)]);

        $instance = new PluginUmdAssetFetcher($plugins, null, null, false, 3);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk(0, [
                self::getUmdFile('TestPlugin1'),
            ]),
            new Chunk(1, [
                self::getUmdFile('TestPlugin2'),
                self::getUmdFile('TestPlugin3'),
            ]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function test_getCatalog_whenLoadingUmdsIndividually()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, true);

        $catalog = $instance->getCatalog();
        $assets = $catalog->getAssets();

        $expectedAssets = [
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin1')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin2')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin3')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin5')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin4')),
        ];

        $this->assertEquals($expectedAssets, $assets);
    }

    public function test_getCatalog_whenRequestingASpecificChunk_andLoadingUmdsIndividually()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, 'TestPlugin4', true);

        $catalog = $instance->getCatalog();
        $assets = $catalog->getAssets();

        $expectedAssets = [
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin4')),
        ];

        $this->assertEquals($expectedAssets, $assets);
    }

    public function test_getCatalog_whenMultipleChunksConfigured()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, false, 3);

        $catalog = $instance->getCatalog();
        $assets = $catalog->getAssets();

        $expectedAssets = [
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin1')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin2')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin3')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin5')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin4')),
        ];

        $this->assertEquals($expectedAssets, $assets);
    }

    public function test_getCatalog_whenRequestingASpecificChunk_andMultipleChunksConfigured()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, '2', false, 3);

        $catalog = $instance->getCatalog();
        $assets = $catalog->getAssets();

        $expectedAssets = [
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin5')),
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin4')),
        ];

        $this->assertEquals($expectedAssets, $assets);
    }

    public function test_getCatalog_whenRequestingASpecificChunk_andMultipleChunksConfigured_andChunkIsZero()
    {
        $plugins = array_keys(self::TEST_PLUGIN_DEPENDENCIES);
        $instance = new PluginUmdAssetFetcher($plugins, null, '0', false, 3);

        $catalog = $instance->getCatalog();
        $assets = $catalog->getAssets();

        $expectedAssets = [
            new OnDiskUIAsset(PIWIK_INCLUDE_PATH, self::getUmdFile('TestPlugin1')),
        ];

        $this->assertEquals($expectedAssets, $assets);

        // check int 0 too
        $instance = new PluginUmdAssetFetcher($plugins, null, 0, false, 3);

        $catalog = $instance->getCatalog();
        $assets = $catalog->getAssets();

        $this->assertEquals($expectedAssets, $assets);
    }

    public function test_orderPluginsByPluginDependencies()
    {
        $pluginList = PluginUmdAssetFetcher::orderPluginsByPluginDependencies([
            'TestPlugin4',
            'TestPlugin1',
            'TestPlugin2',
        ]);
        $this->assertEquals(['TestPlugin4', 'TestPlugin1', 'TestPlugin2'], $pluginList);
    }

    public function test_orderPluginsByPluginDependencies_whenKeepUnresolvedIsFalse()
    {
        $pluginList = PluginUmdAssetFetcher::orderPluginsByPluginDependencies([
            'TestPlugin4',
            'TestPlugin1',
            'TestPlugin2',
        ], $keepUnresolved = false);
        $this->assertEquals(['TestPlugin1', 'TestPlugin2'], $pluginList);
    }

    private static function getUmdFile(string $pluginName)
    {
        $relativeRoot = str_replace(PIWIK_INCLUDE_PATH, '', self::TEST_PLUGINS_DIR);
        $relativeRoot = ltrim($relativeRoot, '/');
        return $relativeRoot . '/' . $pluginName . '/vue/dist/' . $pluginName . '.umd.min.js';
    }
}
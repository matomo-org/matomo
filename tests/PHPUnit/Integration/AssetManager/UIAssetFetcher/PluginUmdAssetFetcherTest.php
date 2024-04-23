<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    public const TEST_PLUGINS_DIR = __DIR__ . '/plugins';

    public const TEST_PLUGIN_UMD_SIZES = [
        'NoPluginUmd' => null,
        'TestPlugin1' => 10,
        'TestPlugin2' => 1,
        'TestPlugin3' => 3,
        'TestPlugin4' => 1,
        'TestPlugin5' => 5,
        'OnDemand1' => 2,
        'OnDemand2' => 3,
        'OnDemand3' => 5,
        'TestPlugin6' => 2,
    ];

    public const TEST_PLUGIN_DEPENDENCIES = [
        'NoPluginUmd' => null,
        'TestPlugin1' => [],
        'TestPlugin2' => ['TestPlugin1'],
        'TestPlugin3' => ['TestPlugin1', 'TestPlugin2'],
        'TestPlugin4' => ['TestPlugin5'],
        'TestPlugin5' => ['TestPlugin1', 'TestPlugin3'],
    ];

    public const TEST_PLUGIN_DEPENDENCIES_ON_DEMAND = [
        'OnDemand1' => ['TestPlugin1'],
        'OnDemand2' => ['TestPlugin1'],
        'OnDemand3' => ['TestPlugin1'],
    ];

    public const TEST_PLUGIN_DEPENDENCIES_DEPENDS_ON_ON_DEMAND = [
        'TestPlugin6' => ['OnDemand1'],
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

            if (array_key_exists($pluginName, self::TEST_PLUGIN_DEPENDENCIES)) {
                $pluginDependencies = self::TEST_PLUGIN_DEPENDENCIES[$pluginName];
            } elseif (array_key_exists($pluginName, self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND)) {
                $pluginDependencies = self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND[$pluginName];
            } else {
                $pluginDependencies = self::TEST_PLUGIN_DEPENDENCIES_DEPENDS_ON_ON_DEMAND[$pluginName];
            }

            $pluginPath = self::TEST_PLUGINS_DIR . '/' . $pluginName;
            $vueDir = $pluginPath . '/vue/dist';
            $vueSrcDir = $pluginPath . '/vue/src';

            Filesystem::mkdir($vueDir);
            Filesystem::mkdir($vueSrcDir);

            $pluginJsonFile = $pluginPath . '/plugin.json';
            file_put_contents($pluginJsonFile, json_encode([
                'name' => $pluginName,
                'description' => "---",
                'version' => "1.0.0",
                'require' => [
                    'matomo' => ">=4.0.0-b1"
                ],
                'license' => "GPL v3+",
            ]));

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

        $allPlugins = array_merge(
            array_keys(self::TEST_PLUGIN_DEPENDENCIES),
            array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND),
            array_keys(self::TEST_PLUGIN_DEPENDENCIES_DEPENDS_ON_ON_DEMAND)
        );
        foreach ($allPlugins as $plugin) {
            Manager::getInstance()->activatePlugin($plugin);
        }
        Manager::getInstance()->loadActivatedPlugins();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        clearstatcache(true);

        putenv("MATOMO_PLUGIN_DIRS={$this->oldPluginDirsEnvVar}");
        $GLOBALS['MATOMO_PLUGIN_DIRS'] = $this->oldPluginDirsGlobal;
        Manager::initPluginDirectories();
    }

    public function testGetChunkFilesWhenLoadingUmdsIndividually()
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

    public function testGetChunkFilesWhenLoadingUmdsIndividuallyAndNotAllPluginsActivated()
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

    public function testGetChunkFilesWhenOneChunkConfigured()
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

    public function testGetChunkFilesWhenNothingConfigured()
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

    public function testGetChunkFilesWhenMultipleChunksConfigured()
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

    public function testGetChunkFilesWhenMultipleChunksConfiguredAndNotAllPluginsActivated()
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

    public function testGetChunkFilesWhenLoadingUmdsIndividuallyAndOnePluginLoadsOnDemandAndOneDependencyIsMissing()
    {
        $plugins = array_merge(array_keys(self::TEST_PLUGIN_DEPENDENCIES), array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND));
        unset($plugins[array_search('TestPlugin1', $plugins)]);
        $instance = new PluginUmdAssetFetcher($plugins, null, null, true);

        $actualChunkFiles = $instance->getChunkFiles();
        $expectedChunkFiles = [
            new Chunk('TestPlugin4', [self::getUmdFile('TestPlugin4')]),
        ];

        $this->assertEquals($expectedChunkFiles, $actualChunkFiles);
    }

    public function testGetChunkFilesWhenLoadingUmdsIndividuallyAndOnePluginLoadsOnDemandAndOneNormalPluginDependsOnOnDemandPlugin()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing plugin dependency: TestPlugin6 requires plugins OnDemand1');

        $plugins = array_merge(
            array_keys(self::TEST_PLUGIN_DEPENDENCIES),
            array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND),
            array_keys(self::TEST_PLUGIN_DEPENDENCIES_DEPENDS_ON_ON_DEMAND)
        );
        $instance = new PluginUmdAssetFetcher($plugins, null, null, true);

        $instance->getChunkFiles();
    }

    public function testGetChunkFilesWhenLoadingUmdsIndividuallyAndOnePluginLoadsOnDemandAndOnDemandDependencyIsMissing()
    {
        $plugins = array_merge(array_keys(self::TEST_PLUGIN_DEPENDENCIES), array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND));
        unset($plugins[array_search('OnDemand2', $plugins)]);
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

    public function testGetChunkFilesWhenLoadingUmdsIndividuallyAndSomePluginsLoadOnDemand()
    {
        $plugins = array_merge(array_keys(self::TEST_PLUGIN_DEPENDENCIES), array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND));
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

    public function testGetChunkFilesWhenOneChunkConfiguredAndSomePluginsLoadOnDemand()
    {
        $plugins = array_merge(array_keys(self::TEST_PLUGIN_DEPENDENCIES), array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND));
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

    public function testGetChunkFilesWhenMultipleChunksConfiguredAndSomePluginsLoadOnDemand()
    {
        $plugins = array_merge(array_keys(self::TEST_PLUGIN_DEPENDENCIES), array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND));
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

    public function testGetCatalogWhenLoadingUmdsIndividually()
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

    public function testGetCatalogWhenRequestingASpecificChunkAndLoadingUmdsIndividually()
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

    public function testGetCatalogWhenMultipleChunksConfigured()
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

    public function testGetCatalogWhenRequestingASpecificChunkAndMultipleChunksConfigured()
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

    public function testGetCatalogWhenRequestingASpecificChunkAndMultipleChunksConfiguredAndChunkIsZero()
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

    public function testOrderPluginsByPluginDependencies()
    {
        $pluginList = PluginUmdAssetFetcher::orderPluginsByPluginDependencies([
            'TestPlugin4',
            'TestPlugin1',
            'TestPlugin2',
        ]);
        $this->assertEquals(['TestPlugin4', 'TestPlugin1', 'TestPlugin2'], $pluginList);
    }

    public function testOrderPluginsByPluginDependenciesWhenKeepUnresolvedIsFalse()
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

    protected function provideContainerConfig()
    {
        return [
            'plugins.shouldLoadOnDemand' => \Piwik\DI::add(array_keys(self::TEST_PLUGIN_DEPENDENCIES_ON_DEMAND)),
        ];
    }
}

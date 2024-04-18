<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager;
use Piwik\AssetManager\UIAssetFetcher\StaticUIAssetFetcher;
use Piwik\Config;
use Piwik\Filesystem;
use Piwik\Plugin;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Unit\AssetManager\PluginManagerMock;
use Piwik\Tests\Unit\AssetManager\PluginMock;
use Piwik\Tests\Unit\AssetManager\ThemeMock;
use Piwik\Tests\Unit\AssetManager\UIAssetCacheBusterMock;

/**
 * @group AssetManagerTest
 */
class AssetManagerTest extends IntegrationTestCase
{
    // todo Theme->rewriteAssetPathIfOverridesFound is not tested

    public const TEST_PLUGINS_DIR = __DIR__ . '/plugins';

    public const ASSET_MANAGER_TEST_DIR = 'tests/PHPUnit/Unit/AssetManager/';

    public const FIRST_CACHE_BUSTER_JS = 'first-cache-buster-js';
    public const SECOND_CACHE_BUSTER_JS = 'second-cache-buster-js';
    public const FIRST_CACHE_BUSTER_SS = 'first-cache-buster-stylesheet';
    public const SECOND_CACHE_BUSTER_SS = 'second-cache-buster-stylesheet';

    public const CORE_PLUGIN_NAME = 'MockCorePlugin';
    public const CORE_PLUGIN_WITHOUT_ASSETS_NAME = 'MockCoreWithoutAssetPlugin';
    public const NON_CORE_PLUGIN_NAME = 'MockNonCorePlugin';
    public const CORE_THEME_PLUGIN_NAME = 'CoreThemePlugin';
    public const NON_CORE_THEME_PLUGIN_NAME = 'NonCoreThemePlugin';
    public const CORE_PLUGIN_WITH_ONLY_UMD_NAME = 'MockCorePluginOnlyUmd';
    public const NON_CORE_PLUGIN_WITH_ONLY_UMD_NAME = 'MockNonCorePluginOnlyUmd';

    private $oldPluginDirsEnvVar;
    private $oldPluginDirsGlobal;

    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * @var UIAsset
     */
    private $mergedAsset;

    /**
     * @var UIAssetCacheBusterMock
     */
    private $cacheBuster;

    /**
     * @var PluginManagerMock
     */
    private $pluginManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpPluginsDirectory();

        $this->setUpConfig();

        $this->activateMergedAssets();

        $this->setUpCacheBuster();

        $this->setUpAssetManager();

        $this->setUpPluginManager();

        $this->setUpTheme();

        $this->setUpPlugins();
    }

    public function tearDown(): void
    {
        $this->removePluginsDirectory();

        if ($this->assetManager !== null) {
            $this->assetManager->removeMergedAssets();
        }

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Plugin\Manager' => \Piwik\DI::autowire('Piwik\Tests\Unit\AssetManager\PluginManagerMock')
        );
    }

    private function setUpPluginsDirectory()
    {
        $this->oldPluginDirsEnvVar = getenv('MATOMO_PLUGIN_DIRS');
        $this->oldPluginDirsGlobal = $GLOBALS['MATOMO_PLUGIN_DIRS'];

        parent::setUpBeforeClass();

        $pluginsWithUmds = [
            self::CORE_PLUGIN_NAME,
            self::NON_CORE_PLUGIN_NAME,
            self::CORE_PLUGIN_WITH_ONLY_UMD_NAME,
            self::NON_CORE_PLUGIN_WITH_ONLY_UMD_NAME,
        ];

        // setup plugin test directories
        Filesystem::unlinkRecursive(self::TEST_PLUGINS_DIR, true);
        foreach ($pluginsWithUmds as $pluginName) {
            $vueDir = self::TEST_PLUGINS_DIR . '/' . $pluginName . '/vue/dist';
            $vueSrcDir = self::TEST_PLUGINS_DIR . '/' . $pluginName . '/vue/src';

            Filesystem::mkdir($vueDir);
            Filesystem::mkdir($vueSrcDir);

            $umdDependencies = [
                "dependsOn" => [],
            ];
            $umdDependenciesPath = $vueDir . '/umd.metadata.json';

            file_put_contents($umdDependenciesPath, json_encode($umdDependencies));

            $umdPath = $vueDir . '/' . $pluginName . '.umd.min.js';
            $umdContent = "// begin $pluginName\n";
            $umdContent .= "// end $pluginName\n";

            file_put_contents($umdPath, $umdContent);
        }

        clearstatcache(true);

        putenv("MATOMO_PLUGIN_DIRS=" . self::TEST_PLUGINS_DIR . ';'
            . str_replace(PIWIK_INCLUDE_PATH, '', self::TEST_PLUGINS_DIR));
        unset($GLOBALS['MATOMO_PLUGIN_DIRS']);
        Manager::initPluginDirectories();
    }

    private function removePluginsDirectory()
    {
        Filesystem::unlinkRecursive(self::TEST_PLUGINS_DIR, true);

        clearstatcache(true);

        putenv("MATOMO_PLUGIN_DIRS={$this->oldPluginDirsEnvVar}");
        $GLOBALS['MATOMO_PLUGIN_DIRS'] = $this->oldPluginDirsGlobal;
        Manager::initPluginDirectories();
    }

    private function activateMergedAssets()
    {
        Config::getInstance()->Development['disable_merged_assets'] = 0;
    }

    private function disableMergedAssets()
    {
        Config::getInstance()->Development['disable_merged_assets'] = 1;
    }

    private function setUpConfig()
    {
        Config::getInstance()->Plugins = array('Plugins' => array('MockCorePlugin', 'CoreThemePlugin'));
        Config::getInstance()->Development['enabled'] = 1;
        Config::getInstance()->General['default_language'] = 'en';

        $this->disableMergedAssets();
    }

    private function setUpCacheBuster()
    {
        $this->cacheBuster = UIAssetCacheBusterMock::getInstance();
    }

    private function setUpAssetManager()
    {
        $this->assetManager = new AssetManager();

        $this->assetManager->removeMergedAssets();

        $this->assetManager->setCacheBuster($this->cacheBuster);
    }

    private function setUpPluginManager()
    {
        $this->pluginManager = Manager::getInstance();
    }

    private function setUpPlugins()
    {
        $this->pluginManager->setPlugins(
            array(
                 new PluginMock('Login'), // PluginUmdAssetFetcher assumes Login is always available
                 $this->getCoreTheme()->getPlugin(),
                 $this->getNonCoreTheme()->getPlugin(),
                 $this->getCorePlugin(),
                 $this->getCorePluginWithoutUIAssets(),
                 $this->getNonCorePlugin(),
                 $this->getCorePluginWithOnlyUmd(),
                 $this->getNonCorePluginWithOnlyUmd(),
            )
        );

        $this->pluginManager->setLoadedTheme($this->getNonCoreTheme());
    }

    private function setUpCorePluginOnly()
    {
        $this->pluginManager->setPlugins(
            array(
                 $this->getCorePlugin(),
            )
        );
    }

    /**
     * @return Plugin
     */
    private function getCorePlugin()
    {
        $corePlugin = new PluginMock(self::CORE_PLUGIN_NAME);

        $corePlugin->setJsFiles(
            array(
                 self::ASSET_MANAGER_TEST_DIR . 'scripts/SimpleObject.js',
                 self::ASSET_MANAGER_TEST_DIR . 'scripts/SimpleArray.js',
            )
        );

        $corePlugin->setStylesheetFiles($this->getCorePluginStylesheetFiles());
        $corePlugin->setJsCustomization('// customization via event');
        $corePlugin->setCssCustomization('/* customization via event */');

        return $corePlugin;
    }

    private function getCorePluginWithOnlyUmd()
    {
        return new PluginMock(self::CORE_PLUGIN_WITH_ONLY_UMD_NAME);
    }

    private function getNonCorePluginWithOnlyUmd()
    {
        return new PluginMock(self::NON_CORE_PLUGIN_WITH_ONLY_UMD_NAME);
    }

    /**
     * @return Plugin
     */
    private function getCorePluginWithoutUIAssets()
    {
        return new PluginMock(self::CORE_PLUGIN_WITHOUT_ASSETS_NAME);
    }

    /**
     * @return Plugin
     */
    private function getNonCorePlugin()
    {
        $nonCorePlugin = new PluginMock(self::NON_CORE_PLUGIN_NAME);
        $nonCorePlugin->setJsFiles(array(self::ASSET_MANAGER_TEST_DIR . 'scripts/SimpleAlert.js'));

        return $nonCorePlugin;
    }

    private function setUpTheme()
    {
        $this->assetManager->setTheme($this->getCoreTheme());
    }

    /**
     * @return ThemeMock
     */
    private function getCoreTheme()
    {
        return $this->createTheme(self::CORE_THEME_PLUGIN_NAME);
    }

    /**
     * @return ThemeMock
     */
    private function getNonCoreTheme()
    {
        return $this->createTheme(self::NON_CORE_THEME_PLUGIN_NAME);
    }

    /**
     * @param string $themeName
     * @return ThemeMock
     */
    private function createTheme($themeName)
    {
        $coreThemePlugin = new PluginMock($themeName);

        $coreThemePlugin->setIsTheme(true);

        $coreTheme = new ThemeMock($coreThemePlugin);

        $coreTheme->setStylesheet($this->getCoreThemeStylesheet());
        $coreTheme->setJsFiles(array(self::ASSET_MANAGER_TEST_DIR . 'scripts/SimpleComments.js'));

        return $coreTheme;
    }

    /**
     * @return string[]
     */
    public function getCorePluginStylesheetFiles()
    {
        return array(
            self::ASSET_MANAGER_TEST_DIR . 'stylesheets/SimpleLess.less',
            self::ASSET_MANAGER_TEST_DIR . 'stylesheets/CssWithURLs.css',
        );
    }

    private function getAssetContent()
    {
        return $this->mergedAsset->getContent();
    }

    /**
     * @param string $cacheBuster
     */
    private function setJSCacheBuster($cacheBuster)
    {
        $this->cacheBuster->setPiwikVersionBasedCacheBuster($cacheBuster);
    }

    /**
     * @param string $cacheBuster
     */
    private function setStylesheetCacheBuster($cacheBuster)
    {
        $this->cacheBuster->setMd5BasedCacheBuster($cacheBuster);
    }

    private function triggerGetMergedCoreJavaScript()
    {
        $this->mergedAsset = $this->assetManager->getMergedCoreJavaScript();
    }

    private function triggerGetMergedNonCoreJavaScript()
    {
        $this->mergedAsset = $this->assetManager->getMergedNonCoreJavaScript();
    }

    private function triggerGetMergedChunkJavaScript()
    {
        $chunks = [];
        for ($i = 0; $i < AssetManager\UIAssetFetcher\PluginUmdAssetFetcher::getDefaultChunkCount(); ++$i) {
            $chunks[] = $this->assetManager->getMergedJavaScriptChunk($i);
        }
        return $chunks;
    }

    private function triggerGetMergedStylesheet()
    {
        $this->mergedAsset = $this->assetManager->getMergedStylesheet();
    }

    private function validateMergedCoreJs()
    {
        $expectedContent = $this->getExpectedMergedCoreJs();

        $this->validateExpectedContent($expectedContent);
    }

    private function validateMergedNonCoreJs()
    {
        $expectedContent = $this->getExpectedMergedNonCoreJs();

        $this->validateExpectedContent($expectedContent);
    }

    private function validateMergedStylesheet()
    {
        $expectedContent = $this->getExpectedMergedStylesheet();

        $this->validateExpectedContent($expectedContent);
    }

    /**
     * @param string $expectedContent
     */
    private function validateExpectedContent($expectedContent)
    {
        file_put_contents('testfile', $this->mergedAsset->getContent());
        $this->assertEquals($expectedContent, $this->mergedAsset->getContent());
    }

    /**
     * @return string
     */
    private function getExpectedMergedCoreJs()
    {
        return $this->getExpectedMergedJs('ExpectedMergeResultCore.js');
    }

    /**
     * @return string
     */
    private function getExpectedMergedNonCoreJs()
    {
        return $this->getExpectedMergedJs('ExpectedMergeResultNonCore.js');
    }

    /**
     * @param string $filename
     * @return string
     */
    private function getExpectedMergedJs($filename)
    {
        $expectedMergeResult = new OnDiskUIAsset(PIWIK_DOCUMENT_ROOT, self::ASSET_MANAGER_TEST_DIR . 'scripts/' . $filename);

        $expectedContent = $expectedMergeResult->getContent();

        return $this->adjustExpectedJsContent($expectedContent);
    }

    /**
     * @param string $expectedJsContent
     * @return string
     */
    private function adjustExpectedJsContent($expectedJsContent)
    {
        return $this->specifyCacheBusterInExpectedContent($expectedJsContent, $this->cacheBuster->piwikVersionBasedCacheBuster());
    }

    /**
     * @return string
     */
    private function getExpectedMergedStylesheet()
    {
        $expectedMergeResult = new OnDiskUIAsset(PIWIK_DOCUMENT_ROOT, self::ASSET_MANAGER_TEST_DIR . 'stylesheets/ExpectedMergeResult.css');

        $expectedContent = $expectedMergeResult->getContent();

        $expectedContent = $this->specifyCacheBusterInExpectedContent($expectedContent, $this->cacheBuster->md5BasedCacheBuster(''));

        return $expectedContent;
    }

    /**
     * @return string
     */
    private function getCoreThemeStylesheet()
    {
        return self::ASSET_MANAGER_TEST_DIR . 'stylesheets/SimpleBody.css';
    }

    /**
     * @param string $content
     * @param string $cacheBuster
     * @return string
     */
    private function specifyCacheBusterInExpectedContent($content, $cacheBuster)
    {
        return str_replace('{{{CACHE-BUSTER-JS}}}', $cacheBuster, $content);
    }

    /**
     * @param string $previousContent
     */
    private function assertAssetContentIsSameAs($previousContent)
    {
        $this->assertEquals($previousContent, $this->getAssetContent());
    }

    /**
     * @param string $previousContent
     */
    private function assertAssetContentChanged($previousContent)
    {
        $this->assertNotEquals($previousContent, $this->getAssetContent());
    }

    /**
     * @return string
     */
    private function getJsTranslationScript()
    {
        return
            '<script type="text/javascript">' . "\n" .
            'var translations = [];' . "\n" .
            'if (typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }for(var i in translations) { piwik_translations[i] = translations[i];} ' . "\n" .
            '</script>';
    }

    /**
     * @return array
     */
    private function generateAllMergedAssets()
    {
        $this->triggerGetMergedStylesheet();
        $stylesheetAsset = $this->mergedAsset;

        $this->triggerGetMergedCoreJavaScript();
        $coreJsAsset = $this->mergedAsset;

        $this->triggerGetMergedNonCoreJavaScript();
        $nonCoreJsAsset = $this->mergedAsset;

        $this->assertTrue($stylesheetAsset->exists());
        $this->assertTrue($coreJsAsset->exists());
        $this->assertTrue($nonCoreJsAsset->exists());

        $chunks = $this->triggerGetMergedChunkJavaScript();
        $this->assertCount(3, $chunks);
        $this->assertTrue($chunks[0]->exists());
        $this->assertTrue($chunks[1]->exists());
        $this->assertTrue($chunks[2]->exists());

        return array($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset, $chunks);
    }

    /**
     * @group Core
     */
    public function test_getMergedCoreJavaScript_NotGenerated()
    {
        $this->setJSCacheBuster(self::FIRST_CACHE_BUSTER_JS);

        $this->triggerGetMergedCoreJavaScript();

        $this->validateMergedCoreJs();
    }

    /**
     * @group Core
     */
    public function test_getMergedNonCoreJavaScript_NotGenerated()
    {
        $this->setJSCacheBuster(self::FIRST_CACHE_BUSTER_JS);

        $this->triggerGetMergedNonCoreJavaScript();

        $this->validateMergedNonCoreJs();
    }

    /**
     * @group Core
     */
    public function test_getMergedNonCoreJavaScript_NotGenerated_NoNonCorePlugin()
    {
        $this->setUpCorePluginOnly();

        $this->setJSCacheBuster(self::FIRST_CACHE_BUSTER_JS);

        $this->triggerGetMergedNonCoreJavaScript();

        $expectedContent = $this->adjustExpectedJsContent('/* Matomo Javascript - cb={{{CACHE-BUSTER-JS}}}*/' . "\n");

        $this->validateExpectedContent($expectedContent);
    }

    /**
     * @group Core
     */
    public function test_getMergedCoreJavaScript_AlreadyGenerated_MergedAssetsDisabled_UpToDate()
    {
        $this->disableMergedAssets();

        $this->setJSCacheBuster(self::FIRST_CACHE_BUSTER_JS);

        $this->triggerGetMergedCoreJavaScript();

        $content = $this->getAssetContent();

        $this->triggerGetMergedCoreJavaScript();

        $this->assertAssetContentIsSameAs($content);
    }

    /**
     * @group Core
     */
    public function test_getMergedCoreJavaScript_AlreadyGenerated_MergedAssetsDeactivated_Stale()
    {
        $this->disableMergedAssets();

        $this->setJSCacheBuster(self::FIRST_CACHE_BUSTER_JS);

        $this->triggerGetMergedCoreJavaScript();

        $content = $this->getAssetContent();

        $this->setJSCacheBuster(self::SECOND_CACHE_BUSTER_JS);

        $this->triggerGetMergedCoreJavaScript();

        $this->assertAssetContentChanged($content);

        $this->validateMergedCoreJs();
    }

    /**
     * @group Core
     */
    public function test_getMergedStylesheet_NotGenerated()
    {
        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $this->validateMergedStylesheet();
    }

    /**
     * We always regenerate if cache buster changes
     * @group Core
     */
    public function test_getMergedStylesheet_Generated_MergedAssetsEnabled_Stale()
    {
        $this->activateMergedAssets();

        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $content = $this->getAssetContent();

        $this->setStylesheetCacheBuster(self::SECOND_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $this->assertAssetContentChanged($content);

        $this->validateMergedStylesheet();
    }

    /**
     * We always regenerate if cache buster changes
     * @group Core
     */
    public function test_getMergedStylesheet_Generated_MergedAssetsDisabled_Stale()
    {
        $this->disableMergedAssets();

        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $content = $this->getAssetContent();

        $this->setStylesheetCacheBuster(self::SECOND_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $this->assertAssetContentChanged($content);

        $this->validateMergedStylesheet();
    }

    /**
     * @group Core
     */
    public function test_getMergedStylesheet_Generated_MergedAssetsDisabled_UpToDate()
    {
        $this->disableMergedAssets();

        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $content = $this->getAssetContent();

        $this->triggerGetMergedStylesheet();

        $this->assertAssetContentIsSameAs($content);
    }

    /**
     * @group Core
     */
    public function test_getCssInclusionDirective()
    {
        $expectedCssInclusionDirective = '<link rel="stylesheet" type="text/css" href="index.php?module=Proxy&action=getCss" />' . "\n";

        $this->assertEquals($expectedCssInclusionDirective, $this->assetManager->getCssInclusionDirective());
    }

    /**
     * @group Core
     */
    public function test_getJsInclusionDirective_MergedAssetsDisabled()
    {
        $this->disableMergedAssets();

        $expectedJsInclusionDirective =
            $this->getJsTranslationScript() .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleObject.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleArray.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleComments.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleAlert.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Integration/plugins/MockCorePlugin/vue/dist/MockCorePlugin.umd.min.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Integration/plugins/MockNonCorePlugin/vue/dist/MockNonCorePlugin.umd.min.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Integration/plugins/MockCorePluginOnlyUmd/vue/dist/MockCorePluginOnlyUmd.umd.min.js"></script>' . "\n" .
            '<script type="text/javascript" src="tests/PHPUnit/Integration/plugins/MockNonCorePluginOnlyUmd/vue/dist/MockNonCorePluginOnlyUmd.umd.min.js"></script>' . "\n";

        $this->assertEquals($expectedJsInclusionDirective, $this->assetManager->getJsInclusionDirective());
    }

    /**
     * @group Core
     */
    public function test_getJsInclusionDirective_MergedAssetsEnabled()
    {
        $expectedJsInclusionDirective =
            $this->getJsTranslationScript() .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getCoreJs"></script>' . "\n" .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getNonCoreJs"></script>' . "\n" .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getUmdJs&chunk=0" defer></script>' . "\n" .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getUmdJs&chunk=1" defer></script>' . "\n" .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getUmdJs&chunk=2" defer></script>' . "\n";

        $this->assertEquals($expectedJsInclusionDirective, $this->assetManager->getJsInclusionDirective());
    }

    /**
     * @group Core
     */
    public function test_getCompiledBaseCss()
    {
        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $staticStylesheetList = array_merge($this->getCorePluginStylesheetFiles(), array($this->getCoreThemeStylesheet()));

        $minimalAssetFetcher = new StaticUIAssetFetcher(
            array_reverse($staticStylesheetList),
            $staticStylesheetList,
            $this->getCoreTheme()
        );

        $this->assetManager->setMinimalStylesheetFetcher($minimalAssetFetcher);

        $this->mergedAsset = $this->assetManager->getCompiledBaseCss();

        $this->validateMergedStylesheet();
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset, $chunks) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets();

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertFalse($coreJsAsset->exists());
        $this->assertFalse($nonCoreJsAsset->exists());

        $this->assertCount(3, $chunks);
        $this->assertFalse($chunks[0]->exists());
        $this->assertFalse($chunks[1]->exists());
        $this->assertFalse($chunks[2]->exists());
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets_PluginNameSpecified_PluginWithoutAssets()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset, $chunks) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets(self::CORE_PLUGIN_WITHOUT_ASSETS_NAME);

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertTrue($coreJsAsset->exists());
        $this->assertTrue($nonCoreJsAsset->exists());

        $this->assertCount(3, $chunks);
        $this->assertTrue($chunks[0]->exists());
        $this->assertTrue($chunks[1]->exists());
        $this->assertTrue($chunks[2]->exists());
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets_PluginNameSpecified_CorePlugin()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset, $chunks) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets(self::CORE_PLUGIN_NAME);

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertFalse($coreJsAsset->exists());
        $this->assertTrue($nonCoreJsAsset->exists());

        $this->assertCount(3, $chunks);
        $this->assertFalse($chunks[0]->exists());
        $this->assertTrue($chunks[1]->exists());
        $this->assertTrue($chunks[2]->exists());
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets_PluginNameSpecified_NonCoreThemeWithAssets()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset, $chunks) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets(self::NON_CORE_THEME_PLUGIN_NAME);

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertTrue($coreJsAsset->exists());
        $this->assertFalse($nonCoreJsAsset->exists());

        $this->assertCount(3, $chunks);
        $this->assertTrue($chunks[0]->exists());
        $this->assertTrue($chunks[1]->exists());
        $this->assertTrue($chunks[2]->exists());
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use PHPUnit_Framework_TestCase;
use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager;
use Piwik\AssetManager\UIAssetFetcher\StaticUIAssetFetcher;
use Piwik\Config;
use Piwik\Plugin;
use Piwik\Plugin\Manager;
use Piwik\EventDispatcher;
use Piwik\Tests\Framework\Mock\TestConfig;
use Piwik\Tests\Unit\AssetManager\PluginManagerMock;
use Piwik\Tests\Unit\AssetManager\PluginMock;
use Piwik\Tests\Unit\AssetManager\ThemeMock;
use Piwik\Tests\Unit\AssetManager\UIAssetCacheBusterMock;

/**
 * @group AssetManagerTest
 */
class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    // todo Theme->rewriteAssetPathIfOverridesFound is not tested

    const ASSET_MANAGER_TEST_DIR = 'tests/PHPUnit/Unit/AssetManager/';

    const FIRST_CACHE_BUSTER_JS = 'first-cache-buster-js';
    const SECOND_CACHE_BUSTER_JS = 'second-cache-buster-js';
    const FIRST_CACHE_BUSTER_SS = 'first-cache-buster-stylesheet';
    const SECOND_CACHE_BUSTER_SS = 'second-cache-buster-stylesheet';

    const CORE_PLUGIN_NAME = 'MockCorePlugin';
    const CORE_PLUGIN_WITHOUT_ASSETS_NAME = 'MockCoreWithoutAssetPlugin';
    const NON_CORE_PLUGIN_NAME = 'MockNonCorePlugin';
    const CORE_THEME_PLUGIN_NAME = 'CoreThemePlugin';
    const NON_CORE_THEME_PLUGIN_NAME = 'NonCoreThemePlugin';

    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * @var UIAsset
     */
    private $mergedAsset;

    /**
     * @var \Piwik\Tests\Unit\AssetManager\UIAssetCacheBusterMock
     */
    private $cacheBuster;

    /**
     * @var \Piwik\Tests\Unit\AssetManager\PluginManagerMock
     */
    private $pluginManager;

    public function setUp()
    {
        $this->activateMergedAssets();

        $this->setUpCacheBuster();

        $this->setUpAssetManager();

        $this->setUpPluginManager();

        $this->setUpTheme();

        $this->setUpPlugins();
    }

    public function tearDown()
    {
        $this->assetManager->removeMergedAssets();
        Manager::unsetInstance();
    }

    private function activateMergedAssets()
    {
        $this->setUpConfig('merged-assets-enabled.ini.php');
    }

    private function disableMergedAssets()
    {
        $this->setUpConfig('merged-assets-disabled.ini.php');
    }

    /**
     * @param string $filename
     */
    private function setUpConfig($filename)
    {
        $userFile = PIWIK_INCLUDE_PATH . '/' . self::ASSET_MANAGER_TEST_DIR . 'configs/' . $filename;
        $globalFile = PIWIK_INCLUDE_PATH . '/' . self::ASSET_MANAGER_TEST_DIR . 'configs/plugins.ini.php';

        Config::setSingletonInstance(new TestConfig($globalFile, $userFile));
    }

    private function setUpCacheBuster()
    {
        $this->cacheBuster = UIAssetCacheBusterMock::getInstance();
    }

    private function setUpAssetManager()
    {
        $this->assetManager = AssetManager::getInstance();

        $this->assetManager->removeMergedAssets();

        $this->assetManager->setCacheBuster($this->cacheBuster);
    }

    private function setUpPluginManager()
    {
        $this->pluginManager = PluginManagerMock::getInstance();
        Manager::setSingletonInstance($this->pluginManager);

        EventDispatcher::unsetInstance(); // EventDispatcher stores a reference to Plugin Manager
    }

    private function setUpPlugins()
    {
        $this->pluginManager->setPlugins(
            array(
                 $this->getCoreTheme()->getPlugin(),
                 $this->getNonCoreTheme()->getPlugin(),
                 $this->getCorePlugin(),
                 $this->getCorePluginWithoutUIAssets(),
                 $this->getNonCorePlugin()
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
        $expectedMergeResult = new OnDiskUIAsset(PIWIK_USER_PATH, self::ASSET_MANAGER_TEST_DIR .'scripts/' . $filename);

        $expectedContent = $expectedMergeResult->getContent();

        return $this->adjustExpectedJsContent($expectedContent);
    }

    /**
     * @param string $expectedJsContent
     * @return string
     */
    private function adjustExpectedJsContent($expectedJsContent)
    {
        $expectedJsContent = str_replace("\n", "\r\n", $expectedJsContent);

        $expectedJsContent = $this->specifyCacheBusterInExpectedContent($expectedJsContent, $this->cacheBuster->piwikVersionBasedCacheBuster());

        return $expectedJsContent;
    }

    /**
     * @return string
     */
    private function getExpectedMergedStylesheet()
    {
        $expectedMergeResult = new OnDiskUIAsset(PIWIK_USER_PATH, self::ASSET_MANAGER_TEST_DIR .'stylesheets/ExpectedMergeResult.css');

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
            '<script type="text/javascript">' . PHP_EOL .
            'var translations = [];' . PHP_EOL .
            'if (typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }for(var i in translations) { piwik_translations[i] = translations[i];} ' . PHP_EOL .
            '</script>';
    }

    /**
     * @return UIAsset[]
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

        return array($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset);
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

        $expectedContent = $this->adjustExpectedJsContent('/* Piwik Javascript - cb={{{CACHE-BUSTER-JS}}}*/' . PHP_EOL);

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
        $expectedCssInclusionDirective = '<link rel="stylesheet" type="text/css" href="index.php?module=Proxy&action=getCss" />' . PHP_EOL;

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
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleObject.js"></script>' . PHP_EOL .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleArray.js"></script>' . PHP_EOL .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleComments.js"></script>' . PHP_EOL .
            '<script type="text/javascript" src="tests/PHPUnit/Unit/AssetManager/scripts/SimpleAlert.js"></script>' . PHP_EOL;

        $this->assertEquals($expectedJsInclusionDirective, $this->assetManager->getJsInclusionDirective());
    }

    /**
     * @group Core
     */
    public function test_getJsInclusionDirective_MergedAssetsEnabled()
    {
        $expectedJsInclusionDirective =
            $this->getJsTranslationScript() .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getCoreJs"></script>' . PHP_EOL .
            '<script type="text/javascript" src="index.php?module=Proxy&action=getNonCoreJs"></script>' . PHP_EOL;

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
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets();

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertFalse($coreJsAsset->exists());
        $this->assertFalse($nonCoreJsAsset->exists());
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets_PluginNameSpecified_PluginWithoutAssets()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets(self::CORE_PLUGIN_WITHOUT_ASSETS_NAME);

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertTrue($coreJsAsset->exists());
        $this->assertTrue($nonCoreJsAsset->exists());
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets_PluginNameSpecified_CorePlugin()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets(self::CORE_PLUGIN_NAME);

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertFalse($coreJsAsset->exists());
        $this->assertTrue($nonCoreJsAsset->exists());
    }

    /**
     * @group Core
     */
    public function test_removeMergedAssets_PluginNameSpecified_NonCoreThemeWithAssets()
    {
        list($stylesheetAsset, $coreJsAsset, $nonCoreJsAsset) = $this->generateAllMergedAssets();

        $this->assetManager->removeMergedAssets(self::NON_CORE_THEME_PLUGIN_NAME);

        $this->assertFalse($stylesheetAsset->exists());
        $this->assertTrue($coreJsAsset->exists());
        $this->assertFalse($nonCoreJsAsset->exists());
    }
}
<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\AssetManager;
use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager\UIAssetFetcher\StaticUIAssetFetcher;
use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\Plugin;

require_once PIWIK_INCLUDE_PATH . "/tests/PHPUnit/Core/AssetManager/UIAssetCacheBusterMock.php";
require_once PIWIK_INCLUDE_PATH . "/tests/PHPUnit/Core/AssetManager/PluginManagerMock.php";
require_once PIWIK_INCLUDE_PATH . "/tests/PHPUnit/Core/AssetManager/PluginMock.php";
require_once PIWIK_INCLUDE_PATH . "/tests/PHPUnit/Core/AssetManager/ThemeMock.php";

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    // todo Theme->rewriteAssetPathIfOverridesFound is not tested

    const ASSET_MANAGER_TEST_DIR = 'tests/PHPUnit/Core/AssetManager/';

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
     * @var UIAssetCacheBusterMock
     */
    private $cacheBuster;

    /**
     * @var PluginManagerMock
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

        $config = Config::getInstance();
        $config->setTestEnvironment($userFile, $globalFile);
        $config->init();
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

    private function clearDateCache()
    {
        clearstatcache();
    }

    /**
     * @return int
     */
    private function waitAndGetModificationDate()
    {
        $this->clearDateCache();

        sleep(1.5);

        $modificationDate = $this->mergedAsset->getModificationDate();

        return $modificationDate;
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
     * @param int $previousDate
     */
    private function validateDateDidNotChange($previousDate)
    {
        $this->clearDateCache();

        $this->assertEquals($previousDate, $this->mergedAsset->getModificationDate());
    }

    /**
     * @param int $previousDate
     */
    private function validateDateIsMoreRecent($previousDate)
    {
        $this->clearDateCache();

        $this->assertTrue($previousDate < $this->mergedAsset->getModificationDate());
    }

    /**
     * @return string
     */
    private function getJsTranslationScript()
    {
        return
            '<script type="text/javascript">' . PHP_EOL .
            'var translations = [];' . PHP_EOL .
            'if(typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }for(var i in translations) { piwik_translations[i] = translations[i];} function _pk_translate(translationStringId) { if( typeof(piwik_translations[translationStringId]) != \'undefined\' ){  return piwik_translations[translationStringId]; }return "The string "+translationStringId+" was not loaded in javascript. Make sure it is added in the Translate.getClientSideTranslationKeys hook.";}' . PHP_EOL .
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

        $modDateBeforeSecondRequest = $this->waitAndGetModificationDate();

        $this->triggerGetMergedCoreJavaScript();

        $this->validateDateDidNotChange($modDateBeforeSecondRequest);
    }

    /**
     * @group Core
     */
    public function test_getMergedCoreJavaScript_AlreadyGenerated_MergedAssetsDisabled_Stale()
    {
        $this->disableMergedAssets();

        $this->setJSCacheBuster(self::FIRST_CACHE_BUSTER_JS);

        $this->triggerGetMergedCoreJavaScript();

        $modDateBeforeSecondRequest = $this->waitAndGetModificationDate();

        $this->setJSCacheBuster(self::SECOND_CACHE_BUSTER_JS);

        $this->triggerGetMergedCoreJavaScript();

        $this->validateDateIsMoreRecent($modDateBeforeSecondRequest);

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
     * @group Core
     */
    public function test_getMergedStylesheet_Generated_MergedAssetsEnabled_Stale()
    {
        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $modDateBeforeSecondRequest = $this->waitAndGetModificationDate();

        $this->setStylesheetCacheBuster(self::SECOND_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $this->validateDateDidNotChange($modDateBeforeSecondRequest);
    }

    /**
     * @group Core
     */
    public function test_getMergedStylesheet_Generated_MergedAssetsDisabled_Stale()
    {
        $this->disableMergedAssets();

        $this->setStylesheetCacheBuster(self::FIRST_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $modDateBeforeSecondRequest = $this->waitAndGetModificationDate();

        $this->setStylesheetCacheBuster(self::SECOND_CACHE_BUSTER_SS);

        $this->triggerGetMergedStylesheet();

        $this->validateDateIsMoreRecent($modDateBeforeSecondRequest);

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

        $modDateBeforeSecondRequest = $this->waitAndGetModificationDate();

        $this->triggerGetMergedStylesheet();

        $this->validateDateDidNotChange($modDateBeforeSecondRequest);
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
            '<script type="text/javascript" src="tests/PHPUnit/Core/AssetManager/scripts/SimpleObject.js"></script>' . PHP_EOL .
            '<script type="text/javascript" src="tests/PHPUnit/Core/AssetManager/scripts/SimpleArray.js"></script>' . PHP_EOL .
            '<script type="text/javascript" src="tests/PHPUnit/Core/AssetManager/scripts/SimpleComments.js"></script>' . PHP_EOL .
            '<script type="text/javascript" src="tests/PHPUnit/Core/AssetManager/scripts/SimpleAlert.js"></script>' . PHP_EOL;

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
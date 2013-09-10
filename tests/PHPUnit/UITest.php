<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Access;
use Piwik\AssetManager;
use Piwik\Plugins\VisitsSummary\API;

abstract class UITest extends IntegrationTestCase
{
    const IMAGE_TYPE = 'png';
    const CAPTURE_PROGRAM = 'phantomjs';
    
    private static $recursiveProxyLinkNames = array('libs', 'plugins', 'tests');

    public static function createAccessInstance()
    {
        Access::setSingletonInstance($access = new Test_Access_OverrideLogin());
        Piwik_PostEvent('FrontController.initAuthenticationObject');
    }
    
    public static function setUpBeforeClass()
    {
        if (self::CAPTURE_PROGRAM == 'slimerjs'
            && !self::isSlimerJsAvailable()
        ) {
            self::markTestSkipped("slimerjs is not available, skipping UI integration tests. "
                                . "(install by downloading http://slimerjs.org/download.html)");
        } else if (self::CAPTURE_PROGRAM == 'phantomjs'
                   && !self::isPhantomJsAvailable()
        ) {
            self::markTestSkipped("phantomjs is not available, skipping UI integration tests. "
                                . "(install by downloading http://phantomjs.org/download.html)");
        }
        
        parent::setUpBeforeClass();
        
        AssetManager::removeMergedAssets();
        
        // launch archiving so tests don't run out of time
        $date = Date::factory(static::$fixture->dateTime)->toString();
        API::getInstance()->get(static::$fixture->idSite, 'year', $date);

        // make sure processed & expected dirs exist
        self::makeDirsAndLinks();

        // run slimerjs/phantomjs w/ all urls so we only invoke it once per 25 entries (travis needs
        // there to be output)
        $urlsToTest = static::getUrlsForTesting();

        reset($urlsToTest);
        for ($i = 0; $i < count($urlsToTest); $i += 25) {
            $urls = array();
            for ($j = $i; $j != $i + 25 && $j < count($urlsToTest); ++$j) {
                list($name, $urlQuery) = current($urlsToTest);

                list($processedScreenshotPath, $expectedScreenshotPath) = self::getProcessedAndExpectedScreenshotPaths($name);
                $urls[] = array($processedScreenshotPath, self::getProxyUrl() . $urlQuery);

                next($urlsToTest);
            }
            
            echo "Generating screenshots...\n";
            self::runCaptureProgram($urls);
        }
    }
    
    public static function tearDownAfterClass()
    {
        if (file_exists("C:\\nppdf32Log\\debuglog.txt")) { // remove slimerjs oddity
            unlink("C:\\nppdf32Log\\debuglog.txt");
        }

        self::removeRecursiveLinks();

        if (!Zend_Registry::get('db')) {
            Piwik::createDatabaseObject();
        }
        
        parent::tearDownAfterClass();
    }
    
    public function setUp()
    {
        parent::setUp();
        
        if (!Zend_Registry::get('db')) {
            Piwik::createDatabaseObject();
        }
    }
    
    public function tearDown()
    {
        parent::tearDown();
        
        \Zend_Registry::get('db')->closeConnection();
        \Zend_Registry::set('db', false);
    }
    
    private static function runCaptureProgram($urlInfo)
    {
        file_put_contents(PIWIK_INCLUDE_PATH . '/tmp/urls.txt', json_encode($urlInfo));
        $cmd = self::CAPTURE_PROGRAM . " \"" . PIWIK_INCLUDE_PATH . "/tests/resources/screenshot-capture/capture.js\" 2>&1";
        
        exec($cmd, $output, $result);
        $output = implode("\n", $output);
        if ($result !== 0
            || strpos($output, "ERROR") !== false
        ) {
            echo self::CAPTURE_PROGRAM . " failed: " . $output . "\n\ncommand used: $cmd\n";
            throw new Exception("phantomjs failed");
        }
        return $output;
    }
    
    protected function compareScreenshot($name, $urlQuery)
    {
        list($expectedPath, $processedPath) = self::getProcessedAndExpectedScreenshotPaths($name);

        $processed = file_get_contents($processedPath);
        
        if (!file_exists($expectedPath)) {
            $this->markTestIncomplete("expected screenshot for processed '$processedPath' is missing");
        }
        
        $expected = file_get_contents($expectedPath);
        if ($expected != $processed) {
            echo "\nFail: '$processedPath' for '$urlQuery'\n";
        }
        $this->assertTrue($expected == $processed, "screenshot compare failed for '$processedPath'");
    }
    
    private static function isSlimerJsAvailable()
    {
        return self::isProgramAvailable('slimerjs');
    }

    private static function isPhantomJsAvailable()
    {
        return self::isProgramAvailable('phantomjs');
    }
    
    private static function isProgramAvailable($name)
    {
        exec($name . ' --help 2>&1', $output, $result);
        return $result === 0 || $result === 1;
    }

    private static function getProcessedAndExpectedScreenshotPaths($name)
    {
        list($processedDir, $expectedDir) = self::getProcessedAndExpectedDirs();

        $outputPrefix = static::getOutputPrefix();

        $processedScreenshotPath = $processedDir . $outputPrefix . '_' . "$name." . self::IMAGE_TYPE;
        $expectedScreenshotPath = $expectedDir . $outputPrefix . '_' . "$name." . self::IMAGE_TYPE;

        return array($processedScreenshotPath, $expectedScreenshotPath);
    }
    
    protected static function getProcessedAndExpectedDirs()
    {
        $path = self::getPathToTestDirectory() . '/../UI';
        return array($path . '/processed-ui-screenshots/', $path . '/expected-ui-screenshots/');
    }
    
    public static function getProxyUrl()
    {
        return Test_Piwik_BaseFixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php';
    }

    private static function makeDirsAndLinks()
    {
        $dirs = array_merge(self::getProcessedAndExpectedDirs(), array(PIWIK_INCLUDE_PATH . '/tmp/sessions'));
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }

        foreach (self::$recursiveProxyLinkNames as $linkName) {
            $linkPath = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/' . $linkName;
            if (!file_exists($linkPath)) {
                symlink(PIWIK_INCLUDE_PATH . '/' . $linkName, $linkPath);
            }
        }
    }

    private static function removeRecursiveLinks()
    {
        foreach (self::$recursiveProxyLinkNames as $linkName) {
            $wholePath = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/' . $linkName;
            if (file_exists($wholePath)) {
                unlink($wholePath);
            }
        }
    }
}
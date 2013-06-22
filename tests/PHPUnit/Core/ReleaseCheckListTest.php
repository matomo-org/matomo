<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class ReleaseCheckListTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->globalConfig = _parse_ini_file(PIWIK_PATH_TEST_TO_ROOT . '/config/global.ini.php', true);
        parent::setUp();
    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testCheckThatConfigurationValuesAreProductionValues()
    {
        $this->_checkEqual(array('Debug' => 'always_archive_data_day'), '0');
        $this->_checkEqual(array('Debug' => 'always_archive_data_period'), '0');
        $this->_checkEqual(array('Debug' => 'enable_sql_profiler'), '0');
        $this->_checkEqual(array('General' => 'time_before_today_archive_considered_outdated'), '10');
        $this->_checkEqual(array('General' => 'enable_browser_archiving_triggering'), '1');
        $this->_checkEqual(array('General' => 'default_language'), 'en');
        $this->_checkEqual(array('Tracker' => 'record_statistics'), '1');
        $this->_checkEqual(array('Tracker' => 'visit_standard_length'), '1800');
        $this->_checkEqual(array('Tracker' => 'trust_visitors_cookies'), '0');
        // logging messages are disabled
        $this->_checkEqual(array('log' => 'logger_message'), '');
        $this->_checkEqual(array('log' => 'logger_exception'), array('screen'));
        $this->_checkEqual(array('log' => 'logger_error'), array('screen'));
        $this->_checkEqual(array('log' => 'logger_api_call'), null);
    }

    private function _checkEqual($key, $valueExpected)
    {
        $section = key($key);
        $optionName = current($key);
        $value = null;
        if (isset($this->globalConfig[$section][$optionName])) {
            $value = $this->globalConfig[$section][$optionName];
        }
        $this->assertEquals($valueExpected, $value, "$section -> $optionName was '" . var_export($value, true) . "', expected '" . var_export($valueExpected, true) . "'");
    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testTemplatesDontContainDebug()
    {
        $patternFailIfFound = '{debug}';
        $files = Piwik::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.tpl');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertFalse(strpos($content, $patternFailIfFound), 'found in ' . $file);
        }
    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testCheckThatGivenPluginsAreDisabledByDefault()
    {
        $pluginsShouldBeDisabled = array(
            'AnonymizeIP',
            'DBStats',
            'SecurityInfo',
            'VisitorGenerator',
        );
        foreach ($pluginsShouldBeDisabled as $pluginName) {
            if (in_array($pluginName, $this->globalConfig['Plugins']['Plugins'])) {
                throw new Exception("Plugin $pluginName is enabled by default but shouldn't.");
            }
        }

    }

    /**
     * test that the profiler is disabled (mandatory on a production server)
     * @group Core
     * @group ReleaseCheckList
     */
    public function testProfilingDisabledInProduction()
    {
        require_once 'Tracker/Db.php';
        $this->assertTrue(Piwik_Tracker_Db::isProfilingEnabled() === false, 'SQL profiler should be disabled in production! See Piwik_Tracker_Db::$profiling');
    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testPiwikTrackerDebugIsOff()
    {
        $this->assertTrue(!isset($GLOBALS['PIWIK_TRACKER_DEBUG']));

        $oldGet = $_GET;
        $_GET = array('idsite' => 1);

        // hiding echoed out message on empty request
        ob_start();
        include PIWIK_PATH_TEST_TO_ROOT . "/piwik.php";
        ob_end_clean();

        $_GET = $oldGet;

        $this->assertTrue($GLOBALS['PIWIK_TRACKER_DEBUG'] === false);
    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testAjaxLibraryVersions()
    {
        Piwik::createConfigObject();
        Piwik_Config::getInstance()->setTestEnvironment();

        $jqueryJs = file_get_contents(PIWIK_DOCUMENT_ROOT . '/libs/jquery/jquery.js', false, NULL, 0, 512);
        $this->assertTrue((boolean)preg_match('/jQuery (?:JavaScript Library )?v?([0-9.]+)/', $jqueryJs, $matches));
        $this->assertEquals(Piwik_Config::getInstance()->General['jquery_version'], $matches[1]);

        $jqueryuiJs = file_get_contents(PIWIK_DOCUMENT_ROOT . '/libs/jquery/jquery-ui.js', false, NULL, 0, 512);
        $this->assertTrue((boolean)preg_match('/jQuery UI (?:- v)?([0-9.]+)/', $jqueryuiJs, $matches));
        $this->assertEquals(Piwik_Config::getInstance()->General['jqueryui_version'], $matches[1]);

    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testEndOfLines()
    {
        if (Piwik_Common::isWindows()) {
            // SVN native does not make this work on windows
            return;
        }
        foreach (Piwik::globr(PIWIK_DOCUMENT_ROOT, '*') as $file) {
            // skip files in these folders
            if (strpos($file, '/.git/') !== false ||
                strpos($file, '/documentation/') !== false ||
                strpos($file, '/tests/') !== false ||
                strpos($file, '/lang/') !== false ||
                strpos($file, 'yuicompressor') !== false ||
                strpos($file, '/tmp/') !== false
            ) {
                continue;
            }

            // skip files with these file extensions
            if (preg_match('/\.(bmp|fdf|gif|deflate|gz|ico|jar|jpg|p12|pdf|png|rar|swf|vsd|z|zip|ttf|so|dat|eps|phar)$/', $file)) {
                continue;
            }

            if (!is_dir($file)) {
                $contents = file_get_contents($file);

                // expect CRLF
                if (preg_match('/\.(bat|ps1)$/', $file)) {
                    $contents = str_replace("\r\n", '', $contents);
                    $this->assertTrue(strpos($contents, "\n") === false, 'Incorrect line endings in ' . $file);
                } else {
                    // expect native
                    $hasWindowsEOL = strpos($contents, "\r\n");

                    // overwrite translations files with incorrect line endings
                    $this->assertTrue($hasWindowsEOL === false, 'Incorrect line endings \r\n found in ' . $file);
                }
            }
        }
    }

    /**
     * @group Core
     * @group ReleaseCheckList
     */
    public function testPiwikJavaScript()
    {
        // check source against Snort rule 8443
        // @see http://dev.piwik.org/trac/ticket/2203
        $pattern = '/\x5b\x5c{2}.*\x5c{2}[\x22\x27]/';
        $contents = file_get_contents(PIWIK_DOCUMENT_ROOT . '/js/piwik.js');

        $this->assertTrue(preg_match($pattern, $contents) == 0);

        $contents = file_get_contents(PIWIK_DOCUMENT_ROOT . '/piwik.js');
        $this->assertTrue(preg_match($pattern, $contents) == 0);
    }
}

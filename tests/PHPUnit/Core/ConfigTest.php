<?php
use Piwik\Config;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testUserConfigOverwritesSectionGlobalConfigValue()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';

        $config = Config::getInstance();
        $config->setTestEnvironment($userFile, $globalFile);
        $config->init();

        $this->assertEquals("value_overwritten", $config->Category['key1']);
        $this->assertEquals("value2", $config->Category['key2']);
        $this->assertEquals('tes"t', $config->GeneralSection['login']);
        $this->assertEquals("value3", $config->CategoryOnlyInGlobalFile['key3']);
        $this->assertEquals("value4", $config->CategoryOnlyInGlobalFile['key4']);

        $expectedArray = array('plugin"1', 'plugin2', 'plugin3');
        $array = $config->TestArray;
        $this->assertEquals($expectedArray, $array['installed']);

        $expectedArray = array('value1', 'value2');
        $array = $config->TestArrayOnlyInGlobalFile;
        $this->assertEquals($expectedArray, $array['my_array']);
    }

    /**
     * @group Core
     */
    public function testWritingConfigWithSpecialCharacters()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.written.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';

        $config = Config::getInstance();
        $config->setTestEnvironment($userFile, $globalFile);
        $config->init();

        $stringWritten = '&6^ geagea\'\'\'";;&';
        $config->Category = array('test' => $stringWritten);
        $this->assertEquals($stringWritten, $config->Category['test']);
        unset($config);

        $config = Config::getInstance();
        $config->setTestEnvironment($userFile, $globalFile);
        $config->init();

        $this->assertEquals($stringWritten, $config->Category['test']);
        $config->Category = array(
            'test'  => $config->Category['test'],
            'test2' => $stringWritten,
        );
        $this->assertEquals($stringWritten, $config->Category['test']);
        $this->assertEquals($stringWritten, $config->Category['test2']);
    }

    /**
     * @group Core
     */
    public function testUserConfigOverwritesGlobalConfig()
    {
        $userFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Config/global.ini.php';

        $config = Config::getInstance();
        $config->setTestEnvironment($userFile, $globalFile);

        $this->assertEquals("value_overwritten", $config->Category['key1']);
        $this->assertEquals("value2", $config->Category['key2']);
        $this->assertEquals("tes\"t", $config->GeneralSection['login']);
        $this->assertEquals("value3", $config->CategoryOnlyInGlobalFile['key3']);
        $this->assertEquals("value4", $config->CategoryOnlyInGlobalFile['key4']);

        $expectedArray = array('plugin"1', 'plugin2', 'plugin3');
        $array = $config->TestArray;
        $this->assertEquals($expectedArray, $array['installed']);

        $expectedArray = array('value1', 'value2');
        $array = $config->TestArrayOnlyInGlobalFile;
        $this->assertEquals($expectedArray, $array['my_array']);

        Config::getInstance()->clear();
    }

    /**
     * Dateprovider for testCompareElements
     */
    public function getCompareElementsData()
    {
        return array(
            array('string = string', array(
                'a', 'a', 0,
            )),
            array('string > string', array(
                'b', 'a', 1,
            )),
            array('string < string', array(
                'a', 'b', -1,
            )),
            array('string vs array', array(
                'a', array('a'), -1,
            )),
            array('array vs string', array(
                array('a'), 'a', 1,
            )),
            array('array = array', array(
                array('a'), array('a'), 0,
            )),
            array('array > array', array(
                array('b'), array('a'), 1,
            )),
            array('array < array', array(
                array('a'), array('b'), -1,
            )),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getCompareElementsData
     */
    public function testCompareElements($description, $test)
    {
        list($a, $b, $expected) = $test;

        $result = Config::compareElements($a, $b);
        $this->assertEquals($expected, $result, $description);
    }

    /**
     * Dataprovider for testArrayUnmerge
     * @return array
     */
    public function getArrayUnmergeData()
    {
        return array(
            array('description of test', array(
                array(),
                array(),
            )),
            array('override with empty', array(
                array('login' => 'root', 'password' => 'b33r'),
                array('password' => ''),
            )),
            array('override with non-empty', array(
                array('login' => 'root', 'password' => ''),
                array('password' => 'b33r'),
            )),
            array('add element', array(
                array('login' => 'root', 'password' => ''),
                array('auth' => 'Login'),
            )),
            array('override with empty array', array(
                array('headers' => ''),
                array('headers' => array()),
            )),
            array('override with array', array(
                array('headers' => ''),
                array('headers' => array('Content-Length', 'Content-Type')),
            )),
            array('override an array', array(
                array('headers' => array()),
                array('headers' => array('Content-Length', 'Content-Type')),
            )),
            array('override similar arrays', array(
                array('headers' => array('Content-Length', 'Set-Cookie')),
                array('headers' => array('Content-Length', 'Content-Type')),
            )),
            array('override dyslexic arrays', array(
                array('headers' => array('Content-Type', 'Content-Length')),
                array('headers' => array('Content-Length', 'Content-Type')),
            )),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getArrayUnmergeData
     */
    public function testArrayUnmerge($description, $test)
    {
        $configWriter = Config::getInstance();

        list($a, $b) = $test;

        $combined = array_merge($a, $b);

        $diff = $configWriter->array_unmerge($a, $combined);

        // expect $b == $diff
        $this->assertEquals(serialize($b), serialize($diff), $description);
    }

    /**
     * Dataprovider for testDumpConfig
     */
    public function getDumpConfigData()
    {
        $header = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n" .
            "; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.\n";

        return array(
            array('global only, not cached', array(
                array(),
                array('General' => array('debug' => '1')),
                array(),
                false,
            )),

            array('global only, cached get', array(
                array(),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '1')),
                false,
            )),

            array('global only, cached set', array(
                array(),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '2')),
                $header . "[General]\ndebug = 2\n\n",
            )),

            array('local copy (same), not cached', array(
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '1')),
                array(),
                false,
            )),

            array('local copy (same), cached get', array(
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '1')),
                false,
            )),

            array('local copy (same), cached set', array(
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '2')),
                $header . "[General]\ndebug = 2\n\n",
            )),

            array('local copy (different), not cached', array(
                array('General' => array('debug' => '2')),
                array('General' => array('debug' => '1')),
                array(),
                false,
            )),

            array('local copy (different), cached get', array(
                array('General' => array('debug' => '2')),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '2')),
                false,
            )),

            array('local copy (different), cached set', array(
                array('General' => array('debug' => '2')),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '3')),
                $header . "[General]\ndebug = 3\n\n",
            )),

            array('local copy, not cached, new section', array(
                array('Tracker' => array('anonymize' => '1')),
                array('General' => array('debug' => '1')),
                array(),
                false,
            )),

            array('local copy, cached get, new section', array(
                array('Tracker' => array('anonymize' => '1')),
                array('General' => array('debug' => '1')),
                array('Tracker' => array('anonymize' => '1')),
                false,
            )),

            array('local copy, cached set local, new section', array(
                array('Tracker' => array('anonymize' => '1')),
                array('General' => array('debug' => '1')),
                array('Tracker' => array('anonymize' => '2')),
                $header . "[Tracker]\nanonymize = 2\n\n",
            )),

            array('local copy, cached set global, new section', array(
                array('Tracker' => array('anonymize' => '1')),
                array('General' => array('debug' => '1')),
                array('General' => array('debug' => '2')),
                $header . "[General]\ndebug = 2\n\n[Tracker]\nanonymize = 1\n\n",
            )),

            array('sort, common sections', array(
                array('Tracker' => array('anonymize' => '1'),
                      'General' => array('debug' => '1')),
                array('General' => array('debug' => '0'),
                      'Tracker' => array('anonymize' => '0')),
                array('Tracker' => array('anonymize' => '2')),
                $header . "[General]\ndebug = 1\n\n[Tracker]\nanonymize = 2\n\n",
            )),

            array('sort, common sections before new section', array(
                array('Tracker' => array('anonymize' => '1'),
                      'General' => array('debug' => '1')),
                array('General' => array('debug' => '0'),
                      'Tracker' => array('anonymize' => '0')),
                array('Segment' => array('dimension' => 'foo')),
                $header . "[General]\ndebug = 1\n\n[Tracker]\nanonymize = 1\n\n[Segment]\ndimension = \"foo\"\n\n",
            )),

            array('change back to default', array(
                array('Tracker' => array('anonymize' => '1')),
                array('Tracker' => array('anonymize' => '0'),
                      'General' => array('debug' => '1')),
                array('Tracker' => array('anonymize' => '0')),
                $header
            )),
        );

    }

    /**
     * @group Core
     * 
     * @dataProvider getDumpConfigData
     */
    public function testDumpConfig($description, $test)
    {
        $config = Config::getInstance();

        list($configLocal, $configGlobal, $configCache, $expected) = $test;

        $output = $config->dumpConfig($configLocal, $configGlobal, $configCache);

        $this->assertEquals($expected, $output, $description);
    }
}


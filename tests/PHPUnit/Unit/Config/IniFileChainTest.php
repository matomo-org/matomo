<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Piwik\Config\IniFileChain;

/**
 * @group Core
 */
class IniFileChainTest extends TestCase
{
    /**
     * Data provider for testCompareElements
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
     * @dataProvider getCompareElementsData
     */
    public function test_compareElements_CorrectlyComparesElements($description, $test)
    {
        list($a, $b, $expected) = $test;

        $result = IniFileChain::compareElements($a, $b);
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
     * @dataProvider getArrayUnmergeData
     */
    public function test_ArrayUnmerge_ReturnsCorrectDiff($description, $test)
    {
        $configWriter = new IniFileChain(array(), null);

        list($a, $b) = $test;

        $combined = array_merge($a, $b);

        $diff = $configWriter->arrayUnmerge($a, $combined);

        // expect $b == $diff
        $this->assertEquals(serialize($b), serialize($diff), $description);
    }

    /**
     * Dataprovider for testArrayUnmerge Invalid Data
     * @return array
     */
    public function getArrayUnmergeInvalidData()
    {
        return array(
            array('modified invalid string', array(
                array(),
                '',
                array(),
            )),
            array('modified invalid int', array(
                array('login' => 'root', 'password' => 'b33r'),
                17,
                array(),

            )),
            array('original invalid string', array(
                '',
                array(),
                array(),
            )),
            array('original invalid int', array(
                17,
                array('login' => 'root', 'password' => 'b33r'),
                array('login' => 'root', 'password' => 'b33r'),
            )),
            array('both invalid', array(
                17,
                '',
                array(),
            )),
        );
    }

    /**
     * @dataProvider getArrayUnmergeInvalidData
     */
    public function test_ArrayUnmerge_CanHandleInvalidData($description, $test)
    {
        $configWriter = new IniFileChain(array(), null);

        list($a, $b, $c) = $test;

        $this->assertEquals($c, $configWriter->arrayUnmerge($a, $b), $description);
    }

    public function getMergingTestData()
    {
        return array(
            array('test default settings are merged recursively',
                array( // default settings
                    __DIR__ . '/test_files/default_settings_1.ini.php',
                    __DIR__ . '/test_files/empty.ini.php',
                    __DIR__ . '/test_files/default_settings_2.ini.php'
                ),
                __DIR__ . '/test_files/empty.ini.php', // user settings
                array( // expected
                    'Section1' => array(
                        'var1' => 'overriddenValue1',
                        'var3' => array(
                            'overriddenValue2',
                            'overriddenValue3'
                        )
                    ),
                    'Section2' => array(
                        'var4' => 'val$ue5'
                    )
                )
            ),

            array('test user settings completely overwrite default',
                array( // default settings
                    __DIR__ . '/test_files/default_settings_1.ini.php'
                ),
                __DIR__ . '/test_files/default_settings_2.ini.php', // user settings
                array( // expected
                    'Section1' => array(
                        'var1' => 'overriddenValue1',
                        'var3' => array(
                            'overriddenValue2',
                            'overriddenValue3'
                        )
                    ),
                    'Section2' => array(
                        'var4' => 'val$ue5'
                    )
                )
            )
        );
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function test_construct_MergesFileData_Correctly($testDescription, $defaultSettingFiles, $userSettingsFile, $expected)
    {
        $fileChain = new IniFileChain($defaultSettingFiles, $userSettingsFile);
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");
    }

    /**
     * @dataProvider getMergingTestData
     */
    public function test_reload_MergesFileData_Correctly($testDescription, $defaultSettingsFiles, $userSettingsFile, $expected)
    {
        $fileChain = new IniFileChain();
        $fileChain->reload($defaultSettingsFiles, $userSettingsFile);
        $this->assertEquals($expected, $fileChain->getAll(), "'$testDescription' failed");
    }

    public function test_get_ReturnsReferenceToSettingsSection()
    {
        $fileChain = new IniFileChain(
            array(__DIR__ . '/test_files/default_settings_1.ini.php')
        );

        $data =& $fileChain->get('Section1');

        $this->assertEquals(array('var1' => 'val"ue2', 'var3' => array('value3', 'value4')), $data);

        $data['var1'] = 'changed';
        $data['var3'][] = 'newValue';

        $this->assertEquals(array('var1' => 'changed', 'var3' => array('value3', 'value4', 'newValue')), $fileChain->get('Section1'));
    }

    public function test_get_ReturnsReferenceToSettingsSection_EvenIfSettingsIsEmpty()
    {
        $fileChain = new IniFileChain(array(__DIR__ . '/test_files/empty.ini.php'));

        $data =& $fileChain->get('Section');
        $this->assertEquals(array(), $data);

        $data['var1'] = 'changed';
        $this->assertEquals(array('var1' => 'changed'), $fileChain->get('Section'));
    }

    public function test_getAll_ReturnsReferenceToAllSettings()
    {
        $fileChain = new IniFileChain();

        $data =& $fileChain->getAll();
        $data['var'] = 'value';

        $this->assertEquals(array('var' => 'value'), $fileChain->getAll());
    }

    public function test_set_CorrectlySetsSettingValue()
    {
        $fileChain = new IniFileChain();

        $fileChain->set('var', 'value');

        $this->assertEquals(array('var' => 'value'), $fileChain->getAll());
    }

    public function test_getFrom_CorrectlyGetsSettingsFromFile_AndNotCurrentModifiedSettings()
    {
        $defaultSettingsPath = __DIR__ . '/test_files/default_settings_1.ini.php';

        $fileChain = new IniFileChain(
            array($defaultSettingsPath),
            __DIR__ . '/test_files/default_settings_2.ini.php'
        );

        $this->assertEquals(array('var1' => 'val"ue2', 'var3' => array('value3', 'value4')), $fileChain->getFrom($defaultSettingsPath, 'Section1'));
    }

    public function test_getFrom_CorrectlyReturnsUnencodedValue()
    {
        $userSettingsPath = __DIR__ . '/test_files/special_values.ini.php';
        $fileChain = new IniFileChain(array(), $userSettingsPath);

        $this->assertEquals(array(
            'value1' => 'a"bc', 'value2' => array('<script>', '${@piwik(crash))}'
        )), $fileChain->getFrom($userSettingsPath, 'Section'));
    }

    public function getTestDataForDumpTest()
    {
        return array(
            array(
                array( // default settings
                    __DIR__ . '/test_files/default_settings_1.ini.php'
                ),
                __DIR__ . '/test_files/default_settings_2.ini.php', // user settings
                "; some header\n",
                "; some header\n[Section1]\nvar1 = \"overriddenValue1\"\nvar3[] = \"overriddenValue2\"\nvar3[] = \"overriddenValue3\"\n\n[Section2]\nvar4 = \"val&#36;ue5\"\n\n",
                "; some header\n[Section1]\nvar1 = \"overriddenValue1\"\nvar3[] = \"overriddenValue2\"\nvar3[] = \"overriddenValue3\"\n\n"
            )
        );
    }

    /**
     * @dataProvider getTestDataForDumpTest
     */
    public function test_dump_CorrectlyGeneratesIniString_ForAllCurrentSettings(
        $defaultSettingsFiles,
        $userSettingsFile,
        $header,
        $expectedDump
    ) {
        $fileChain = new IniFileChain($defaultSettingsFiles, $userSettingsFile);

        $actualOutput = $fileChain->dump($header);
        $this->assertEquals($expectedDump, $actualOutput);
    }

    /**
     * @dataProvider getTestDataForDumpTest
     */
    public function test_dumpChanges_CorrectlyGeneratesMinimalUserSettingsIniString(
        $defaultSettingsFiles,
        $userSettingsFile,
        $header,
        $expectedDump,
        $expectedDumpChanges
    ) {
        $fileChain = new IniFileChain($defaultSettingsFiles, $userSettingsFile);

        $actualOutput = $fileChain->dumpChanges($header);
        $this->assertEquals($expectedDumpChanges, $actualOutput);
    }

    public function getTestDataForDumpSortTest()
    {
        return array(
            array(
                array( // default settings
                    __DIR__ . '/test_files/default_settings_3.ini.php',
                    __DIR__ . '/test_files/default_settings_2.ini.php',
                    __DIR__ . '/test_files/default_settings_1.ini.php',
                ),
                __DIR__ . '/test_files/empty.ini.php',
                array(
                    'Custom' => array('var' => 'val'),
                    'Settings0' => array('abc' => 'def2'),
                    'Section1' => array('var1' => '5$'),
                    'Settings3' => array('var1' => '2'),
                    'Section2' => array('var4' => '9')
                ),
                "; some header\n",
                "; some header\n[Settings3]\nvar1 = \"2\"\n\n[Settings0]\nabc = \"def2\"\n\n[Section1]\nvar1 = \"5&#36;\"\n\n[Section2]\nvar4 = \"9\"\n\n[Custom]\nvar = \"val\"\n\n"
            )
        );
    }

    /**
     * @dataProvider getTestDataForDumpSortTest
     */
    public function test_dumpChanges_CorrectlySortsSections_ByWhenTheyAppearInConfigFiles(
        $defaultSettingsFiles,
        $userSettingsFile,
        $changesToApply,
        $header,
        $expectedDumpChanges
    ) {
        $fileChain = new IniFileChain($defaultSettingsFiles, $userSettingsFile);

        foreach ($changesToApply as $sectionName => $section) {
            $fileChain->set($sectionName, $section);
        }

        $actualOutput = $fileChain->dumpChanges($header);
        $this->assertEquals($expectedDumpChanges, $actualOutput);
    }


    public function test_dump_handlesSpecialCharsCorrectly()
    {
        $config = new IniFileChain();
        $config->set('first', ["a[]\n\n[d]\n\nb=4" => "\n\n[def]\na=b"]);
        $config->set('second', ["a[]\n\n[d]b=4" => 'b']);
        $config->set('thir][d]', ['a' => 'b']);
        $config->set("four]\n\n[def]\n", ['d[]' => 'e']);
        $out = $config->dump();

        $expected = <<<END
[first]
a[][d]b4 = "

[def]
a=b"

[second]
a[][d]b4 = "b"

[third]
a = "b"

[fourdef]
d[] = "e"


END;

        $this->assertEquals($expected, $out);
    }
}

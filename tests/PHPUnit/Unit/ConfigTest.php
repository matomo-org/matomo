<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Exception\MissingFilePermissionException;

class DumpConfigTestMockIniFileChain extends Config\IniFileChain
{
    public function __construct($settingsChain, $mergedSettings)
    {
        parent::__construct();

        $this->settingsChain = $settingsChain;
        $this->mergedSettings = $mergedSettings;
    }
}

class MockIniSettingsProvider extends GlobalSettingsProvider
{
    public function __construct($configLocal, $configGlobal, $configCommon, $configCache)
    {
        parent::__construct();

        $this->iniFileChain = new DumpConfigTestMockIniFileChain(
            array(
            $this->pathGlobal => $configGlobal,
            $this->pathCommon => $configCommon,
            $this->pathLocal  => $configLocal,
            ),
            $configCache
        );
    }
}

class DumpConfigTestMockConfig extends Config
{
    public function __construct($configLocal, $configGlobal, $configCommon, $configCache)
    {
        parent::__construct(new MockIniSettingsProvider($configLocal, $configGlobal, $configCommon, $configCache));
    }
}

/**
 * @group Core
 */
class ConfigTest extends TestCase
{
    public function testUserConfigOverwritesSectionGlobalConfigValue()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.ini.php';
        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

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

        $expectedArray = array('value1', 'value2');
        $array = $config->TestArrayOnlyInGlobalFile;
        $this->assertEquals($expectedArray, $array['my_array']);
    }

    public function testCommonConfigOverrides()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.config.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

        $this->assertEquals("valueCommon", $config->Category['key2'], var_export($config->Category['key2'], true));
        $this->assertEquals("test", $config->GeneralSection['password']);
        $this->assertEquals("commonValue", $config->TestOnlyInCommon['value']);
    }

    public function testWritingConfigWithSpecialCharacters()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.written.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile));

        $stringWritten = '&6^ geagea\'\'\'";;&';
        $config->Category = array('test' => $stringWritten);
        $this->assertEquals($stringWritten, $config->Category['test']);

        // This will write the file
        $config->forceSave();

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile));

        $this->assertEquals($stringWritten, $config->Category['test']);
        $config->Category = array(
          'test'  => $config->Category['test'],
          'test2' => $stringWritten,
        );
        $this->assertEquals($stringWritten, $config->Category['test']);
        $this->assertEquals($stringWritten, $config->Category['test2']);
    }

    public function testUserConfigOverwritesGlobalConfig()
    {
        $userFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Config/global.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile));

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
    }

    /**
     * Dataprovider for testDumpConfig
     */
    public function getDumpConfigData()
    {
        $header = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n" .
          "; file automatically generated or modified by Matomo; you can manually override the default values in global.ini.php by redefining them in this file.\n";

        return array(
            // Test name, array(
            //   LOCAL
            //   GLOBAL
            //   COMMON
            //   CACHE
            //   --> EXPECTED <--
            array(
              'global only, not cached',
              array(
                array(),                                  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array(),
                false,
              )
            ),

            array(
              'global only, cached get',
              array(
                array(),                                  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array('General' => array('debug' => 1)),
                false,
              )
            ),

            array(
              'global only, cached set',
              array(
                array(),                                  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array('General' => array('debug' => 2)),
                $header . "[General]\ndebug = 2\n\n",
              )
            ),

            array(
              'local copy (same), not cached',
              array(
                array('General' => array('debug' => 1)),  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array(),
                false,
              )
            ),

            array(
              'local copy (same), cached get',
              array(
                array('General' => array('debug' => 1)),  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array('General' => array('debug' => 1)),
                false,
              )
            ),

            array(
              'local copy (same), cached set',
              array(
                array('General' => array('debug' => 1)),  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                    // common
                array('General' => array('debug' => 2)),
                $header . "[General]\ndebug = 2\n\n",
              )
            ),

            array(
              'local copy (different), not cached',
              array(
                array('General' => array('debug' => 2)),  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array(),
                false,
              )
            ),

            array(
              'local copy (different), cached get',
              array(
                array('General' => array('debug' => 2)),  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array('General' => array('debug' => 2)),
                false,
              )
            ),

            array(
              'local copy (different), cached set',
              array(
                array('General' => array('debug' => 2)),  // local
                array('General' => array('debug' => 1)),  // global
                array(),                                  // common
                array('General' => array('debug' => 3)),
                $header . "[General]\ndebug = 3\n\n",
              )
            ),

            array(
              'local copy, not cached, new section',
              array(
                array('Tracker' => array('anonymize' => 1)),  // local
                array('General' => array('debug' => 1)),      // global
                array(),                                      // common
                array(),
                false,
              )
            ),

            array(
              'local copy, cached get, new section',
              array(
                array('Tracker' => array('anonymize' => 1)),  // local
                array('General' => array('debug' => 1)),      // global
                array(),                                      // common
                array('Tracker' => array('anonymize' => 1)),
                false,
              )
            ),

            array(
              'local copy, cached set local, new section',
              array(
                array('Tracker' => array('anonymize' => 1)),  // local
                array('General' => array('debug' => 1)),      // global
                array(),                                      // common
                array('Tracker' => array('anonymize' => 2)),
                $header . "[Tracker]\nanonymize = 2\n\n",
              )
            ),

            array(
              'local copy, cached set global, new section',
              array(
                array('Tracker' => array('anonymize' => 1)),  // local
                array('General' => array('debug' => 1)),      // global
                array(),                                      // common
                array('General' => array('debug' => 2), 'Tracker' => array('anonymize' => 1)),
                $header . "[General]\ndebug = 2\n\n[Tracker]\nanonymize = 1\n\n",
              )
            ),

            array(
              'sort, common sections',
              array(
                array(
                  'Tracker' => array('anonymize' => 1),   // local
                  'General' => array('debug' => 1)
                ),
                array(
                  'General' => array('debug' => 0),       // global
                  'Tracker' => array('anonymize' => 0)
                ),
                array(),                                      // common
                array(
                  'Tracker' => array('anonymize' => 2),
                  'General' => array('debug' => 1)
                ),
                $header . "[General]\ndebug = 1\n\n[Tracker]\nanonymize = 2\n\n",
              )
            ),

            array(
              'sort, common sections before new section',
              array(
                array(
                  'Tracker' => array('anonymize' => 1),   // local
                  'General' => array('debug' => 1)
                ),
                array(
                  'General' => array('debug' => 0),       // global
                  'Tracker' => array('anonymize' => 0)
                ),
                array(),                                      // common
                array(
                  'Segment' => array('dimension' => 'foo'),
                  'Tracker' => array('anonymize' => 1),   // local
                  'General' => array('debug' => 1)
                ),
                $header . "[General]\ndebug = 1\n\n[Tracker]\nanonymize = 1\n\n[Segment]\ndimension = \"foo\"\n\n",
              )
            ),

            array(
              'change back to default',
              array(
                array('Tracker' => array('anonymize' => 1)),  // local
                array(
                  'Tracker' => array('anonymize' => 0),   // global
                  'General' => array('debug' => 1)
                ),
                array(),                                        // common
                array('Tracker' => array('anonymize' => 0)),
                $header
              )
            ),

            array(
              '[General] trusted_hosts has been updated and only this one is written',
              array(
                array('General' => array('trusted_hosts' => 'someRandomHostToOverwrite')),  // local
                array(
                  'General' => array(
                    'settingGlobal' => 'global',   // global
                    'settingCommon' => 'global',
                    'trusted_hosts' => 'none'
                  )
                ),
                array(
                  'General' => array(
                    'settingCommon'  => 'common',       // common
                    'settingCommon2' => 'common'
                  )
                ),
                array('General' => array('trusted_hosts' => 'works')),
                $header . "[General]\ntrusted_hosts = \"works\"\n\n",
              )
            ),

            // Same as above but without trusted_hosts default value in global.ini.php
            // Also, settingCommon3 is the same in the local file as in common, so it is not written out
            array(
              'trusted_hosts and settingCommon3 changed ',
              array(
                array('General' => array('trusted_hosts' => 'someRandomHostToOverwrite')), // local
                array(
                  'General' => array(
                    'settingGlobal' => 'global',                   // global
                    'settingCommon' => 'global'
                  )
                ),
                array(
                  'General' => array(
                    'settingCommon'  => 'common',                   // common
                    'settingCommon2' => 'common',
                    'settingCommon3' => 'common3'
                  )
                ),
                array(
                  'General' => array(
                    'trusted_hosts'  => 'works',               // common
                    'settingCommon2' => 'common', // will be cleared since it's same as in common
                    'settingCommon3' => 'commonOverridenByLocal'
                  )
                ),
                $header . "[General]\ntrusted_hosts = \"works\"\nsettingCommon3 = \"commonOverridenByLocal\"\n\n",
              )
            ),

            // the value in [General]->key has changed
            // the value in [CommonCategory]->newSetting has changed,
            //         but  [CommonCategory]->settingCommon2 hasn't so it is not written
            array(
              'Common tests file',
              array(
                array('General' => array('key' => 'value')),                            // local
                array(
                  'General'        => array('key' => 'global'),                            // global
                  'CommonCategory' => array('settingGlobal' => 'valueGlobal')
                ),
                array(
                  'CommonCategory' => array(
                    'settingCommon'  => 'common',            // common
                    'settingCommon2' => 'common2'
                  )
                ),
                array(
                  'CommonCategory' => array(
                    'settingCommon2' => 'common2',
                    'newSetting'     => 'newValue'
                  ),
                  'General'        => array('key' => 'value')
                ),
                $header . "[General]\nkey = \"value\"\n\n[CommonCategory]\nnewSetting = \"newValue\"\n\n",
              )
            ),

            array(
              'Converts Dollar Sign To Dollar Entity',
              array(
                array('General' => array('key' => '$value', 'key2' => '${value}')),      // local
                array(
                  'General'        => array('key' => '$global'),                            // global
                  'CommonCategory' => array('settingGlobal' => 'valueGlobal')
                ),
                array(
                  'CommonCategory' => array(
                    'settingCommon'  => 'common',            // common
                    'settingCommon2' => 'common2'
                  )
                ),
                array(
                  'CommonCategory' => array(
                    'settingCommon2' => 'common2',
                    'newSetting'     => 'newValue'
                  ),
                  'General'        => array('key' => '$value', 'key2' => '${value}')
                ),
                $header . "[General]\nkey = \"&#36;value\"\nkey2 = \"&#36;{value}\"\n\n[CommonCategory]\nnewSetting = \"newValue\"\n\n",
              )
            ),
        );
    }

    /**
     * @dataProvider getDumpConfigData
     */
    public function testDumpConfig($description, $test)
    {
        list($configLocal, $configGlobal, $configCommon, $configCache, $expected) = $test;

        $config = new DumpConfigTestMockConfig($configLocal, $configGlobal, $configCommon, $configCache);

        $output = $config->dumpConfig();
        $this->assertEquals($expected, $output, $description);
    }

    public function testDollarEntityGetsConvertedToDollarSign()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.config.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

        $this->assertEquals('${@piwik(crash))}', $config->Category['key3']);
    }

    public function testForceSaveWritesNothingIfThereAreNoChanges()
    {
        $sourceConfigFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $configFile = PIWIK_INCLUDE_PATH . '/tmp/tmp.config.ini.php';

        if (file_exists($configFile)) {
            @unlink($configFile);
        }
        copy($sourceConfigFile, $configFile);

        $config = new Config(new GlobalSettingsProvider($sourceConfigFile, $configFile));
        $config->forceSave();

        $this->assertEquals(file_get_contents($sourceConfigFile), file_get_contents($configFile));

        if (file_exists($configFile)) {
            @unlink($configFile);
        }
    }

    public function testFromGlobalConfig()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.config.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

        $configCategory = $config->getFromGlobalConfig('Category');
        $this->assertEquals('value1', $configCategory['key1']);
        $this->assertEquals('value2', $configCategory['key2']);
        $this->assertEquals(array('key1' => 'value1', 'key2' => 'value2'), $configCategory);
    }

    public function testFromCommonConfig()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.config.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

        $configCategory = $config->getFromCommonConfig('Category');
        $this->assertEquals(array('key2' => 'valueCommon', 'key3' => '${@piwik(crash))}'), $configCategory);
    }

    public function testFromLocalConfig()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.config.ini.php';

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

        $configCategory = $config->getFromLocalConfig('Category');
        $this->assertEquals(array('key1' => 'value_overwritten'), $configCategory);
    }

    public function testSanityCheckFails()
    {
        $userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
        $globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';
        $commonFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/common.config.ini.php';

        $expectedPath = '';
        $correctContent = file_get_contents($userFile);
        $incorrectContent = 'incorrrect content';

        \Piwik\Piwik::addAction('Core.configFileSanityCheckFailed', function ($path) use (&$expectedPath) {
            $expectedPath = $path;
        });

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));

        $this->assertFalse($config->sanityCheck($userFile, $incorrectContent, true));
        $this->assertTrue($config->sanityCheck($userFile, $correctContent));
        $this->assertSame($userFile, $expectedPath);
    }

    public function testCheckConfigIsWritableNotThrowsExceptionWhenWritable()
    {
        $this->assertTrue($this->checkConfigIsWritable('Config'));
    }

    public function testCheckConfigIsWritableThrowsExceptionWhenNotWritable()
    {
        $this->expectException(MissingFilePermissionException::class);
        $this->expectExceptionMessage('ConfigFileIsNotWritable');
        $this->checkConfigIsWritable('ConfigNotExists');
    }

    private function checkConfigIsWritable(string $directory): bool
    {
        $userFile = PIWIK_INCLUDE_PATH . "/tests/resources/$directory/config.ini.php";
        $globalFile = PIWIK_INCLUDE_PATH . "/tests/resources/$directory/global.ini.php";
        $commonFile = PIWIK_INCLUDE_PATH . "/tests/resources/$directory/common.config.ini.php";

        $config = new Config(new GlobalSettingsProvider($globalFile, $userFile, $commonFile));
        $config->checkConfigIsWritable();
        return true;
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use __PHP_Incomplete_Class;
use Exception;
use PHPUnit\Framework\TestCase;
use Piwik\Application\Environment;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Log;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\Tests\Framework\Mock\FakeLogger;

/**
 * @backupGlobals enabled
 * @group         Common
 */
class CommonTest extends TestCase
{
    public function testGetProcessId()
    {
        $this->assertEquals(getmypid(), Common::getProcessId());
        //assure always returns same value
        $this->assertEquals(getmypid(), Common::getProcessId());
    }

    public function testHashEquals()
    {
        $this->assertFalse(Common::hashEquals('foo', 'bar'));
        $this->assertFalse(Common::hashEquals('foo', 'fo'));
        $this->assertFalse(Common::hashEquals('foo', 'fooo'));
        $this->assertFalse(Common::hashEquals('foo', 'foa'));
        $this->assertFalse(Common::hashEquals('foo', 'eoo'));
        $this->assertFalse(Common::hashEquals('foo', ''));
        $this->assertFalse(Common::hashEquals('', 'bar'));
        $this->assertTrue(Common::hashEquals('foo', 'foo'));
    }

    /**
     * Dataprovider for testSanitizeInputValues
     */
    public function getInputValues()
    {
        return [ // input, output
                 // sanitize an array OK
                 [
                     ['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52],
                     ['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52],
                 ],
                 [
                     [
                         'test1' => 't1',
                         't45',
                         'teatae',
                         4568,
                         ['test'],
                         1.52,
                         ['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52],
                         ['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52],
                         [
                             [[['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52]]],
                         ],
                     ],
                     [
                         'test1' => 't1',
                         't45',
                         'teatae',
                         4568,
                         ['test'],
                         1.52,
                         ['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52],
                         ['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52],
                         [
                             [[['test1' => 't1', 't45', 'teatae', 4568, ['test'], 1.52]]],
                         ],
                     ],
                 ],
                 // sanitize an array with bad value level1
                 [
                     ['test1' => 't1', 't45', 'tea1"ta"e', 568, 1 => ['t<e"st'], 1.52],
                     ['test1' => 't1', 't45', 'tea1&quot;ta&quot;e', 568, 1 => ['t&lt;e&quot;st'], 1.52],
                 ],
                 // sanitize an array with bad value level2
                 [
                     ['tea1"ta"e' => ['t<e"st' => ['tgeag454554"t']], 1.52],
                     ['tea1&quot;ta&quot;e' => ['t&lt;e&quot;st' => ['tgeag454554&quot;t']], 1.52],
                 ],
                 // sanitize a string unicode => no change
                 [
                     ' Поиск в Интернете  Поgqegиск страниц на рgeqg8978усском',
                     ' Поиск в Интернете  Поgqegиск страниц на рgeqg8978усском',
                 ],
                 // sanitize a bad string
                 [
                     '& " < > 123abc\'',
                     '&amp; &quot; &lt; &gt; 123abc&#039;',
                 ],
                 // test filter - expect new line and null byte to be filtered out
                 [
                     "Null\0Byte",
                     'NullByte',
                 ],
                 // double encoded - no change (document as user error)
                 [
                     '%48%45%4C%00%4C%4F+%57%4F%52%4C%44',
                     '%48%45%4C%00%4C%4F+%57%4F%52%4C%44',
                 ],
                 // sanitize an integer
                 ['121564564', '121564564'],
                 ['121564564.0121', '121564564.0121'],
                 [121564564.0121, 121564564.0121],
                 [12121, 12121],
                 // sanitize HTML
                 [
                     "<test toto='mama' piwik=\"cool\">Piwik!!!!!</test>",
                     '&lt;test toto=&#039;mama&#039; piwik=&quot;cool&quot;&gt;Piwik!!!!!&lt;/test&gt;',
                 ],
                 // sanitize a SQL query
                 [
                     "SELECT piwik FROM piwik_tests where test= 'super\"value' AND cool=toto #comment here",
                     'SELECT piwik FROM piwik_tests where test= &#039;super&quot;value&#039; AND cool=toto #comment here',
                 ],
                 // sanitize php variables
                 [true, true],
                 [false, false],
                 [null, null],
                 ['', ''],
        ];
    }

    /**
     * @dataProvider getInputValues
     */
    public function testSanitizeInputValues($input, $output)
    {
        $this->assertEquals($output, Common::sanitizeInputValues($input));
    }

    /**
     * emptyvarname => exception
     */
    public function testGetRequestVarEmptyVarName()
    {
        $this->expectException(Exception::class);
        $_GET[''] = 1;
        Common::getRequestVar('');
    }

    /**
     * nodefault Notype Novalue => exception
     */
    public function testGetRequestVarNoDefaultNoTypeNoValue()
    {
        $this->expectException(Exception::class);
        Common::getRequestVar('test');
    }

    /**
     * nodefault Notype WithValue => value
     */
    public function testGetRequestVarNoDefaultNoTypeWithValue()
    {
        $_GET['test'] = 1413.431413;
        $this->assertEquals($_GET['test'], Common::getRequestVar('test'));
    }

    /**
     * @dataProvider getValidFloatValues
     */
    public function testGetRequestVarFloatValues($requestValue, $expectedValue)
    {
        $_GET['test'] = $requestValue;
        $value        = Common::getRequestVar('test', null, 'string');
        $this->assertEquals($expectedValue, $value);
    }

    public function getValidFloatValues(): iterable
    {
        yield 'Integer value' => [17, 17.0];
        yield 'Float value' => [17.123, 17.123];
        yield 'String value' => ['17.123', 17.123];
        yield 'Negative string value' => ['-17.123', -17.123];
        yield 'Positive string value' => ['+17.123', 17.123];
        yield 'String value, only fraction digits' => ['.123', 0.123];
        yield 'String value, no fraction digits' => ['123.e-3', 0.123];
        yield 'Negative string value, only fraction digits' => ['-.123', -0.123];
        yield 'Exp value' => [2e-2, 0.02];
        yield 'String exp value' => ['2e-3', 0.002];
        yield 'String Exp value' => ['1.2E-26', 1.2E-26];
        yield 'Octal exp value' => [0123e-2, 1.23];
        yield 'Octal value as string' => ['0123', 123.0];
        yield 'String value with many digits' => ['1254254645455484545.1', 1.2542546454554847E+18];
        yield 'String value with many fraction digits' => ['14.051545421864646123', 14.051545421864645];
    }

    public function testGetRequestVarGetStringIntegerGiven()
    {
        $_GET['test'] = 1413;
        $value        = Common::getRequestVar('test', null, 'string');
        $this->assertEquals('1413', $value);
    }

    /**
     * nodefault Withtype WithValue => exception cos type not matching
     */
    public function testGetRequestVarNoDefaultWithTypeWithValue()
    {
        $this->expectException(Exception::class);
        $this->expectDeprecationMessage("The parameter 'test' isn't set in the Request");
        $_GET['test'] = false;
        Common::getRequestVar('test', null, 'string');
    }

    /**
     * nodefault Withtype WithValue => exception cos type not matching
     */
    public function testGetRequestVarNoDefaultWithTypeWithValue2()
    {
        $this->expectException(Exception::class);
        Common::getRequestVar('test', null, 'string');
    }

    /**
     * Dataprovider for testGetRequestVar
     */
    public function getRequestVarValues()
    {
        return [ // value of request var, default value, var type, expected
                 [1413.431413, 2, 'int', 2],
                 // withdefault Withtype WithValue => value casted as type
                 [null, 'default', null, 'default'],
                 // withdefault Notype NoValue => default value
                 [null, 'default', 'string', 'default'],
                 // withdefault Withtype NoValue =>default value casted as type
                 // integer as a default value / types
                 ['', 45, 'int', 45],
                 [1413.431413, 45, 'int', 45],
                 ['', 45, 'integer', 45],
                 ['', 45.0, 'float', 45.0],
                 ['', 45.25, 'float', 45.25],
                 // string as a default value / types
                 ['1413.431413', 45, 'int', 45],
                 ['1413.431413', 45, 'string', '1413.431413'],
                 ['', 45, 'string', '45'],
                 ['', 'geaga', 'string', 'geaga'],
                 ['', '&#039;}{}}{}{}&#039;', 'string', '&#039;}{}}{}{}&#039;'],
                 ['', 'http://url?arg1=val1&arg2=val2', 'string', 'http://url?arg1=val1&amp;arg2=val2'],
                 [
                     'http://url?arg1=val1&arg2=val2',
                     'http://url?arg1=val1&arg2=val4',
                     'string',
                     'http://url?arg1=val1&amp;arg2=val2',
                 ],
                 [['test', 1345524, ['gaga']], [], 'array', ['test', 1345524, ['gaga']]],
                 // array as a default value / types
                 [['test', 1345524, ['gaga']], 45, 'string', '45'],
                 [['test', 1345524, ['gaga']], [1], 'array', ['test', 1345524, ['gaga']]],
                 [
                     ['test', 1345524, "Start of hello\nworld\n\t", ['gaga']],
                     [1],
                     'array',
                     ['test', 1345524, "Start of hello\nworld\n\t", ['gaga']],
                 ],
                 [['test', 1345524, ['gaga']], 4, 'int', 4],
                 ['', [1], 'array', [1]],
                 ['', [], 'array', []],
                 // we give a number in a string and request for a number => it should give the string casted as a number
                 ['45645646', 1, 'int', 45645646],
                 ['45645646', 45, 'integer', 45645646],
                 ['45645646', '45454', 'string', '45645646'],
                 ['45645646', [], 'array', []],
        ];
    }

    /**
     * @dataProvider getRequestVarValues
     * @group        Core
     */
    public function testGetRequestVar($varValue, $default, $type, $expected)
    {
        $_GET['test'] = $varValue;
        $return       = Common::getRequestVar('test', $default, $type);
        $this->assertEquals($expected, $return);
        // validate correct type
        switch ($type) {
            case 'int':
            case 'integer':
                $this->assertTrue(is_int($return));
                break;
            case 'float':
                $this->assertTrue(is_float($return));
                break;
            case 'string':
                $this->assertTrue(is_string($return));
                break;
            case 'array':
                $this->assertTrue(is_array($return));
                break;
        }
    }

    public function testIsValidFilenameValidValues()
    {
        $valid = [
            'test',
            'test.txt',
            'test.......',
            'en-ZHsimplified',
            '0',
        ];
        foreach ($valid as $toTest) {
            $this->assertTrue(Filesystem::isValidFilename($toTest), $toTest . ' not valid!');
        }
    }

    public function testIsValidFilenameNotValidValues()
    {
        $notvalid = [
            '../test',
            '/etc/htpasswd',
            '$var',
            ';test',
            '[bizarre]',
            '',
            false,
            '.htaccess',
            'very long long eogaioge ageja geau ghaeihieg heiagie aiughaeui hfilename',
            'WHITE SPACE',
        ];
        foreach ($notvalid as $toTest) {
            self::assertFalse(Filesystem::isValidFilename($toTest), $toTest . " valid but shouldn't!");
        }
    }

    public function testSafeUnserialize()
    {
        // should unserialize an allowed class
        $this->assertTrue(Common::safe_unserialize('O:12:"Piwik\Common":0:{}', ['Piwik\Common']) instanceof Common);

        // not allowed classed should result in an incomplete class
        $this->assertTrue(Common::safe_unserialize('O:12:"Piwik\Common":0:{}') instanceof __PHP_Incomplete_Class);

        // strings not unserializable should return false and trigger a debug log
        $logger = $this->createFakeLogger();
        self::assertFalse(Common::safe_unserialize('{1:somebroken}'));
        self::assertStringContainsString(
            'Unable to unserialize a string: unserialize(): Error at offset 0 of 14 bytes',
            $logger->output
        );
    }

    private function createFakeLogger()
    {
        $logger = new FakeLogger();

        $newEnv = new Environment('test', [
            Log\LoggerInterface::class => $logger,
            'Tests.log.allowAllHandlers' => true,
        ]);
        $newEnv->init();

        $newMonologLogger = $newEnv->getContainer()->make(Log\LoggerInterface::class);
        $oldLogger        = new Log($newMonologLogger);
        Log::setSingletonInstance($oldLogger);

        return $logger;
    }

    /**
     * Dataprovider for testGetBrowserLanguage
     */
    public function getBrowserLanguageData()
    {
        return [ // user agent, browser language
                 ['en-gb', 'en-gb'],

                 // filter quality attribute
                 ['en-us,en;q=0.5', 'en-us,en'],

                 // bad user agents
                 ['en-us,chrome://global/locale/intl.properties', 'en-us'],

                 // unregistered language tag
                 ['en,en-securid', 'en'],
                 ['en-securid,en', 'en'],
                 ['en-us,en-securid,en', 'en-us,en'],

                 // accept private sub tags
                 ['en-us,x-en-securid', 'en-us,x-en-securid'],
                 ['en-us,en-x-securid', 'en-us,en-x-securid'],

                 // filter arbitrary white space
                 ['en-us, en', 'en-us,en'],
                 ['en-ca, en-us ,en', 'en-ca,en-us,en'],

                 // handle comments
                 [' ( comment ) en-us (another comment) ', 'en-us'],

                 // handle quoted pairs (embedded in comments)
                 [' ( \( start ) en-us ( \) end ) ', 'en-us'],
                 [' ( \) en-ca, \( ) en-us ( \) ,en ) ', 'en-us'],
        ];
    }

    /**
     * @dataProvider getBrowserLanguageData
     * @group        Core
     */
    public function testGetBrowserLanguage($useragent, $browserLanguage)
    {
        $res = Common::getBrowserLanguage($useragent);
        $this->assertEquals($browserLanguage, $res);
    }

    /**
     * Dataprovider for testExtractCountryCodeFromBrowserLanguage
     */
    public function getCountryCodeTestData()
    {
        /** @var RegionDataProvider $regionDataProvider */
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        return [ // browser language, valid countries, expected result
                 ['', [], 'xx'],
                 ['', ['us' => 'amn'], 'xx'],
                 ['en', ['us' => 'amn'], 'xx'],
                 ['en-us', ['us' => 'amn'], 'us'],
                 ['en-ca', ['us' => 'amn'], 'xx'],
                 ['en-ca', ['us' => 'amn', 'ca' => 'amn'], 'ca'],
                 ['fr-fr,fr-ca', ['us' => 'amn', 'ca' => 'amn'], 'ca'],
                 ['fr-fr;q=1.0,fr-ca;q=0.9', ['us' => 'amn', 'ca' => 'amn'], 'ca'],
                 ['fr-ca,fr;q=0.1', ['us' => 'amn', 'ca' => 'amn'], 'ca'],
                 ['en-us,en;q=0.5', $regionDataProvider->getCountryList(), 'us'],
                 ['fr-ca,fr;q=0.1', ['fr' => 'eur', 'us' => 'amn', 'ca' => 'amn'], 'ca'],
                 ['fr-fr,fr-ca', ['fr' => 'eur', 'us' => 'amn', 'ca' => 'amn'], 'fr'],
        ];
    }

    /**
     * @dataProvider getCountryCodeTestData
     * @group        Core
     */
    public function testExtractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $expected)
    {
        $this->assertEquals(
            $expected,
            Common::extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, true)
        );
        $this->assertEquals(
            $expected,
            Common::extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, false)
        );
    }

    /**
     * Dataprovider for testExtractCountryCodeFromBrowserLanguageInfer
     */
    public function getCountryCodeTestDataInfer()
    {
        return [ // browser language, valid countries, expected result (non-guess vs guess)
                 ['fr,en-us', ['us' => 'amn', 'ca' => 'amn'], 'us', 'fr'],
                 ['fr,en-us', ['fr' => 'eur', 'us' => 'amn', 'ca' => 'amn'], 'us', 'fr'],
                 ['fr,fr-fr,en-us', ['fr' => 'eur', 'us' => 'amn', 'ca' => 'amn'], 'fr', 'fr'],
                 ['fr-fr,fr,en-us', ['fr' => 'eur', 'us' => 'amn', 'ca' => 'amn'], 'fr', 'fr'],
        ];
    }

    /**
     * @dataProvider getCountryCodeTestDataInfer
     * @group        Core
     */
    public function testExtractCountryCodeFromBrowserLanguageInfer(
        $browserLanguage,
        $validCountries,
        $expected,
        $expectedInfer
    ) {
        // do not infer country from language
        $this->assertEquals(
            $expected,
            Common::extractCountryCodeFromBrowserLanguage(
                $browserLanguage,
                $validCountries,
                $enableLanguageToCountryGuess = false
            )
        );

        // infer country from language
        $this->assertEquals(
            $expectedInfer,
            Common::extractCountryCodeFromBrowserLanguage(
                $browserLanguage,
                $validCountries,
                $enableLanguageToCountryGuess = true
            )
        );
    }

    /**
     * Dataprovider for testExtractLanguageAndRegionCodeFromBrowserLanguage
     */
    public function getLanguageDataToExtractLanguageRegionCode()
    {
        return [
            // browser language, valid languages (with optional region), expected result
            ['fr-ca', ['fr'], 'fr'],
            ['fr-ca', ['ca'], 'xx'],
            ['', [], 'xx'],
            ['', ['en'], 'xx'],
            ['fr', ['en'], 'xx'],
            ['en', ['en'], 'en'],
            ['en', ['en-ca'], 'xx'],
            ['en-ca', ['en-ca'], 'en-ca'],
            ['en-ca', ['en'], 'en'],
            ['fr,en-us', ['fr', 'en'], 'fr'],
            ['fr,en-us', ['en', 'fr'], 'fr'],
            ['fr-fr,fr-ca', [], 'fr-fr'],
            ['fr-fr,fr-ca', ['fr-ca'], 'fr-ca'],
            ['-ca', ['fr', 'ca'], 'xx'],
            ['fr-fr;q=1.0,fr-ca;q=0.9', ['fr-ca'], 'fr-ca'],
            ['es,en,fr;q=0.7,de;q=0.3', ['fr', 'es', 'de', 'en'], 'es'],
            ['zh-sg,de;q=0.3', ['zh', 'es', 'de'], 'zh'],
            ['fr-ca,fr;q=0.1', ['fr-ca'], 'fr-ca'],
            ['r5,fr;q=1,de', ['fr', 'de'], 'fr'],
            ['Zen§gq1', ['en'], 'xx'],
            ['zh-hans-cn', ['zh-cn'], 'zh-cn'],
            ['zh-hant-tw', ['zh-tw'], 'zh-tw'],
            ['az-cyrl-az', ['az'], 'az'],
            ['shi-tfng-ma', ['shi', 'shi-ma'], 'shi-ma'],
        ];
    }

    /**
     * @dataProvider getLanguageDataToExtractLanguageRegionCode
     */
    public function testExtractLanguageAndRegionCodeFromBrowserLanguage($browserLanguage, $validLanguages, $expected)
    {
        $this->assertEquals(
            $expected,
            Common::extractLanguageAndRegionCodeFromBrowserLanguage($browserLanguage, $validLanguages),
            "test with {$browserLanguage} failed, expected {$expected}"
        );
    }


    /**
     * Dataprovider for testExtractLanguageCodeFromBrowserLanguage
     */
    public function getLanguageDataToExtractLanguageCode()
    {
        return [
            // browser language, valid languages, expected result
            ['fr-ca', ['fr'], 'fr'],
            ['fr-ca', ['ca'], 'xx'],
            ['', ['en'], 'xx'],
            ['fr', ['en'], 'xx'],
            ['en', ['en'], 'en'],
            ['en', ['en-ca'], 'xx'],
            ['en-ca', ['en'], 'en'],
            ['fr,en-us', ['fr', 'en'], 'fr'],
            ['fr,en-us', ['en', 'fr'], 'fr'],
            ['fr-fr,fr-ca', ['fr'], 'fr'],
            ['-ca', ['fr', 'ca'], 'xx'],
            ['es,en,fr;q=0.7,de;q=0.3', ['fr', 'es', 'de', 'en'], 'es'],
            ['zh-sg,de;q=0.3', ['zh', 'es', 'de'], 'zh'],
            ['r5,fr;q=1,de', ['fr', 'de'], 'fr'],
            ['Zen§gq1', ['en'], 'xx'],
        ];
    }

    /**
     * @dataProvider getLanguageDataToExtractLanguageCode
     */
    public function testExtractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages, $expected)
    {
        $this->assertEquals(
            $expected,
            Common::extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages),
            "test with {$browserLanguage} failed, expected {$expected}"
        );
    }

    /**
     * @dataProvider getLanguageChainTestData
     */
    public function testGetLanguageChain($expected, $browserLanguage)
    {
        self::assertEquals(
            $expected,
            Common::extractLanguageAndRegionCodeFromBrowserLanguage(
                Common::getBrowserLanguage($browserLanguage),
                APILanguagesManager::getInstance()->getAvailableLanguages()
            )
        );
    }

    public function getLanguageChainTestData(): array
    {
        return [
            ['en', 'en-US,en;q=0.8,de-DE;q=0.6,de;q=0.4'],
            ['de', 'de-DE;q=0.6,de;q=0.4'],
            ['zh-cn', 'zh-CN,zh;q=0.9'],
            ['fr', 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3'],
            ['fr', 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7'],
            ['de', 'de,en-US;q=0.7,en;q=0.3'],
            ['pt-br', 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,ru;q=0.6'],
            ['hi', 'hi;en-US,en;q=0.8,q=0.6'],
            ['ta', 'ta-IN,ta'],
            ['hi', 'hi-in,hi,en-us,en'],
            ['th', 'th-TH,th;q=0.9,en;q=0.8'],
            ['zh-tw', 'zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7'],
            ['fa', 'fa,en-US;q=0.9,en;q=0.8,fa-IR;q=0.7'],
            ['nl', 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7'],
            ['it', 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7'],
        ];
    }

    /**
     * @dataProvider getTableNames
     */
    public function testUnprefixTable($prefix, $tableName)
    {
        $originalValue = Config::getInstance()->database['tables_prefix'];

        Config::getInstance()->database['tables_prefix'] = $prefix;

        $manipulatedName = Common::unprefixTable(Common::prefixTable($tableName));

        Config::getInstance()->database['tables_prefix'] = $originalValue;

        self::assertEquals($tableName, $manipulatedName);
    }

    public function getTableNames(): array
    {
        return [
            ['matomo_', 'session'],
            ['blob_', 'archive_blob_2014_07'],
            ['14_', 'archive_blob_2014_07'],
            ['ic_', 'archive_numeric_2014_07'],
            ['log_', 'log_link_visit_action'],
        ];
    }
}

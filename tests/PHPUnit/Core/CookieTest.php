<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class CookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testJsonSerialize
     */
    public function getJsonSerializeData()
    {
        return array(
            array('null', null),
            array('bool false', false),
            array('bool true', true),
            array('negative int', -42),
            array('zero', 0),
            array('positive int', 42),
            array('float', 1.25),
            array('empty string', ''),
            array('nul in string', "\0"),
            array('carriage return in string', "first line\r\nsecond line"),
            array('utf7 in string', 'hello, world'),
            array('utf8 in string', '是'),
            array('empty array', array()),
            array('single element array', array("test")),
            array('associative array', array("alpha", 2 => "beta")),
            array('mixed keys', array('first' => 'john', 'last' => 'doe', 10 => 'age')),
            array('nested arrays', array('top' => array('middle' => 2, array('bottom'), 'last'), 'the end' => true)),
            array('array confusion', array('"', "'", '}', ';', ':')),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getJsonSerializeData
     */
    public function testJsonSerialize($testData, $id)
    {
        // @see http://bugs.php.net/38680
        if (PHP_VERSION >= '5.2.0' && PHP_VERSION < '5.2.1') {
            $this->markTestSkipped('see http://bugs.php.net/38680');
        }

        $this->assertEquals($testData, json_decode(json_encode($testData), $assoc = true), $id);
    }

    /**
     * Dataprovider for testSafeSerialize
     */
    public function getSafeSerializeData()
    {
        return array(
            array('null', null),
            array('bool false', false),
            array('bool true', true),
            array('negative int', -42),
            array('zero', 0),
            array('positive int', 42),
            array('float', 1.25),
            array('empty string', ''),
            array('nul in string', "\0"),
            array('carriage return in string', "first line\r\nsecond line"),
            array('utf7 in string', 'hello, world'),
            array('utf8 in string', '是'),
            array('empty array', array()),
            array('single element array', array("test")),
            array('associative array', array("alpha", 2 => "beta")),
            array('mixed keys', array('first' => 'john', 'last' => 'doe', 10 => 'age')),
            array('nested arrays', array('top' => array('middle' => 2, array('bottom'), 'last'), 'the end' => true)),
            array('array confusion', array('"', "'", '}', ';', ':')),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider getSafeSerializeData
     */
    public function testSafeSerialize($id, $testData)
    {
        $this->assertEquals(serialize($testData), safe_serialize($testData), $id);
        $this->assertEquals($testData, unserialize(safe_serialize($testData)), $id);
        $this->assertSame($testData, safe_unserialize(safe_serialize($testData)), $id);
        $this->assertSame($testData, safe_unserialize(serialize($testData)), $id);
    }

    /**
     * @group Core
     */
    public function testSafeUnserialize()
    {
        /*
         * serialize() uses its internal maachine representation when floats expressed in E-notation,
         * which may vary between php versions, OS, and hardware platforms
         */
        $testData = $tests['exp float'] = -5.0E+142;
        // intentionally disabled; this doesn't work
//        $this->assertEquals( safe_serialize($testData), serialize($testData) );
        $this->assertEquals($testData, unserialize(safe_serialize($testData)));
        $this->assertSame($testData, safe_unserialize(safe_serialize($testData)));
        // workaround: cast floats into strings
        $this->assertSame($testData, safe_unserialize(serialize($testData)));

        $unserialized = array(
            'announcement' => true,
            'source'       => array(
                array(
                    'filename' => 'php-5.3.3.tar.bz2',
                    'name'     => 'PHP 5.3.3 (tar.bz2)',
                    'md5'      => '21ceeeb232813c10283a5ca1b4c87b48',
                    'date'     => '22 July 2010',
                ),
                array(
                    'filename' => 'php-5.3.3.tar.gz',
                    'name'     => 'PHP 5.3.3 (tar.gz)',
                    'md5'      => '5adf1a537895c2ec933fddd48e78d8a2',
                    'date'     => '22 July 2010',
                ),
            ),
            'date'         => '22 July 2010',
            'version'      => '5.3.3',
        );
        $serialized = 'a:4:{s:12:"announcement";b:1;s:6:"source";a:2:{i:0;a:4:{s:8:"filename";s:17:"php-5.3.3.tar.bz2";s:4:"name";s:19:"PHP 5.3.3 (tar.bz2)";s:3:"md5";s:32:"21ceeeb232813c10283a5ca1b4c87b48";s:4:"date";s:12:"22 July 2010";}i:1;a:4:{s:8:"filename";s:16:"php-5.3.3.tar.gz";s:4:"name";s:18:"PHP 5.3.3 (tar.gz)";s:3:"md5";s:32:"5adf1a537895c2ec933fddd48e78d8a2";s:4:"date";s:12:"22 July 2010";}}s:4:"date";s:12:"22 July 2010";s:7:"version";s:5:"5.3.3";}';

        $this->assertSame($unserialized, unserialize($serialized));
        $this->assertEquals($serialized, serialize($unserialized));

        $this->assertSame($unserialized, safe_unserialize($serialized));
        $this->assertEquals($serialized, safe_serialize($unserialized));
        $this->assertSame($unserialized, safe_unserialize(safe_serialize($unserialized)));
        $this->assertEquals($serialized, safe_serialize(safe_unserialize($serialized)));

        $a = 'O:31:"Test_Piwik_Cookie_Phantom_Class":0:{}';
        $this->assertFalse(safe_unserialize($a), "test: unserializing an object where class not (yet) defined");

        $a = 'O:28:"Test_Piwik_Cookie_Mock_Class":0:{}';
        $this->assertFalse(safe_unserialize($a), "test: unserializing an object where class is defined");

        $a = 'a:1:{i:0;O:28:"Test_Piwik_Cookie_Mock_Class":0:{}}';
        $this->assertFalse(safe_unserialize($a), "test: unserializing nested object where class is defined");

        $a = 'a:2:{i:0;s:4:"test";i:1;O:28:"Test_Piwik_Cookie_Mock_Class":0:{}}';
        $this->assertFalse(safe_unserialize($a), "test: unserializing another nested object where class is defined");

        $a = 'O:28:"Test_Piwik_Cookie_Mock_Class":1:{s:34:"' . "\0" . 'Test_Piwik_Cookie_Mock_Class' . "\0" . 'name";s:4:"test";}';
        $this->assertFalse(safe_unserialize($a), "test: unserializing object with member where class is defined");

        // arrays and objects cannot be used as keys, i.e., generates "Warning: Illegal offset type ..."
        $a = 'a:2:{i:0;a:0:{}O:28:"Test_Piwik_Cookie_Mock_Class":0:{}s:4:"test";';
        $this->assertFalse(safe_unserialize($a), "test: unserializing with illegal key");
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Ini;

use Piwik\Ini\IniReader;

class IniReaderTest extends \PHPUnit_Framework_TestCase
{
    public function test_readString()
    {
        $ini = <<<INI
[Section 1]
foo = "bar"
bool_true_1 = 1
bool_false_1 = 0
bool_true_2 = true
bool_false_2 = false
bool_true_3 = yes
bool_false_3 = no
bool_true_4 = on
bool_false_4 = off
empty =
explicit_null = null
int = 10
float = 10.3
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0
array[] = true
array[] = false

[Section 2]
foo = "bar"
INI;
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
                'bool_true_1' => 1,
                'bool_false_1' => 0,
                'bool_true_2' => true,
                'bool_false_2' => false,
                'bool_true_3' => true,
                'bool_false_3' => false,
                'bool_true_4' => true,
                'bool_false_4' => false,
                'empty' => null,
                'explicit_null' => null,
                'int' => 10,
                'float' => 10.3,
                'array' => array(
                    'string',
                    10.3,
                    1,
                    0,
                    true,
                    false,
                ),
            ),
            'Section 2' => array(
                'foo' => 'bar',
            ),
        );
        $reader = new IniReader();
        $this->assertSame($expected, $reader->readString($ini));
    }

    public function test_readString_withEmptyString()
    {
        $reader = new IniReader();
        $this->assertSame(array(), $reader->readString(''));
    }

    /**
     * @expectedException \Piwik\Ini\IniReadingException
     * @expectedExceptionMessage Syntax error in INI configuration
     */
    public function test_readString_shouldThrowException_ifInvalidIni()
    {
        $reader = new IniReader();
        $reader->readString('[ test = foo');
    }

    public function test_readString_shouldIgnoreComments()
    {
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $ini = <<<INI
; <?php exit; ?> DO NOT REMOVE THIS LINE
[Section 1]
foo = "bar"
INI;
        $reader = new IniReader();
        $this->assertSame($expected, $reader->readString($ini));
    }
}

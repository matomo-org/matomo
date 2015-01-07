<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Ini;

use Piwik\Ini\IniWriter;

class IniWriterTest extends \PHPUnit_Framework_TestCase
{
    public function test_writeToString()
    {
        $config = array(
            'Section 1' => array(
                'foo' => 'bar',
                'bool_true' => true,
                'bool_false' => false,
                'int' => 10,
                'float' => 10.3,
                'array' => array(
                    'string',
                    10.3,
                    true,
                    false,
                ),
            ),
            'Section 2' => array(
                'foo' => 'bar',
            ),
        );
        $expected = <<<INI
[Section 1]
foo = "bar"
bool_true = 1
bool_false = 0
int = 10
float = 10.3
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0

[Section 2]
foo = "bar"


INI;
        $writer = new IniWriter();
        $this->assertEquals($expected, $writer->writeToString($config));
    }

    public function test_writeToString_withEmptyConfig()
    {
        $writer = new IniWriter();
        $this->assertEquals('', $writer->writeToString(array()));
    }

    /**
     * @expectedException \Piwik\Ini\IniWritingException
     * @expectedExceptionMessage Section "Section 1" doesn't contain an array of values
     */
    public function test_writeToString_shouldThrowException_withInvalidConfig()
    {
        $writer = new IniWriter();
        $writer->writeToString(array('Section 1' => 123));
    }

    public function test_writeToString_shouldAddHeader()
    {
        $header = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
        $config = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $expected = <<<INI
; <?php exit; ?> DO NOT REMOVE THIS LINE
[Section 1]
foo = "bar"


INI;
        $writer = new IniWriter();
        $this->assertEquals($expected, $writer->writeToString($config, $header));
    }
}

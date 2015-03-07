<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Test\Unit\Formatter;

use DateTime;
use Piwik\Plugins\Monolog\Formatter\LineMessageFormatter;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Formatter\LineMessageFormatter
 */
class LineMessageFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_format_with_placeholders()
    {
        $formatter = new LineMessageFormatter('%level% %tag% %datetime% %message%');

        $record = array(
            'message'    => 'Hello world',
            'datetime'   => DateTime::createFromFormat('U', 0),
            'level_name' => 'ERROR',
            'extra'      => array(
                'class' => 'Foo'
            ),
        );

        $formatted = "ERROR Foo 1970-01-01 00:00:00 Hello world\n";

        $this->assertEquals($formatted, $formatter->format($record));
    }

    /**
     * @test
     */
    public function it_should_insert_request_id_if_defined()
    {
        $formatter = new LineMessageFormatter('%message%');

        $record = array(
            'message'    => 'Hello world',
            'datetime'   => DateTime::createFromFormat('U', 0),
            'level_name' => 'ERROR',
            'extra'      => array(
                'request_id' => 'request id'
            ),
        );

        $formatted = "[request id] Hello world\n";

        $this->assertEquals($formatted, $formatter->format($record));
    }

    /**
     * @test
     */
    public function it_should_indent_multiline_message()
    {
        $formatter = new LineMessageFormatter('%message%');

        $record = array(
            'message'    => "Hello world\ntest\ntest",
            'datetime'   => DateTime::createFromFormat('U', 0),
            'level_name' => 'ERROR',
        );

        $formatted = <<<LOG
Hello world
  test
  test

LOG;

        $this->assertEquals($formatted, $formatter->format($record));
    }
}

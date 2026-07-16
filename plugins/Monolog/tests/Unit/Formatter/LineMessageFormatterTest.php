<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Formatter;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Piwik\Plugins\Monolog\Formatter\LineMessageFormatter;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Formatter\LineMessageFormatter
 */
class LineMessageFormatterTest extends \PHPUnit\Framework\TestCase
{
    public function testItShouldFormatWithPlaceholders()
    {
        $formatter = new LineMessageFormatter('%level% %tag% %datetime% %message%');

        $record = $this->makeRecord('Hello world', array('class' => 'Foo'));

        $formatted = "ERROR Foo 1970-01-01 00:00:00 GMT+0000 Hello world\n";

        $this->assertEquals($formatted, $formatter->format($record));
    }

    public function testItShouldInsertRequestIdIfDefined()
    {
        $formatter = new LineMessageFormatter('%message%');

        $record = $this->makeRecord('Hello world', array('request_id' => 'request id'));

        $formatted = "[request id] Hello world\n";

        $this->assertEquals($formatted, $formatter->format($record));
    }

    public function testItShouldIndentMultilineMessage()
    {
        $formatter = new LineMessageFormatter('%level% %message%');

        $record = $this->makeRecord("Hello world\ntest\x0Atest");

        $formatted = <<<LOG
ERROR Hello world
  test
  test

LOG;

        $this->assertEquals($formatted, $formatter->format($record));
    }

    public function testItShouldSplitInlineLineBreaksIntoManyMessagesIfDisabled()
    {
        $formatter = new LineMessageFormatter('%level% %message%', $allowInlineLineBreaks = false);

        $record = $this->makeRecord("Hello world\ntest\ntest", array('request_id' => '1234'));

        $formatted = <<<LOG
ERROR [1234] Hello world
ERROR [1234] test
ERROR [1234] test

LOG;

        $this->assertEquals($formatted, $formatter->format($record));
    }

    public function testItShouldEscapeControlCharacters()
    {
        $formatter = new LineMessageFormatter('%level% %message%', $allowInlineLineBreaks = false);

        $record = $this->makeRecord("Hello world\x1Btest\ntest\x1Btest");

        $formatted = <<<LOG
ERROR Hello world\\033test
ERROR test\\033test

LOG;

        $this->assertEquals($formatted, $formatter->format($record));
    }

    private function makeRecord(string $message, array $extra = array()): LogRecord
    {
        return new LogRecord(
            DateTimeImmutable::createFromFormat('U', '0'),
            'logger',
            Level::Error,
            $message,
            array(),
            $extra
        );
    }
}

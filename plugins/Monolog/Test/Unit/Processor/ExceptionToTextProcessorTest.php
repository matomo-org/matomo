<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Test\Unit\Processor;

use Piwik\Log;
use Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor
 */
class ExceptionToTextProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_skip_if_no_exception()
    {
        $processor = new ExceptionToTextProcessor();

        $record = array('message' => 'Hello world');

        $this->assertEquals($record, $processor($record));
    }

    /**
     * @test
     */
    public function it_should_replace_message_with_formatted_exception()
    {
        $processor = new ExceptionToTextProcessor();
        Log::$debugBacktraceForTests = '[stack trace]';

        $exception = new \Exception('Hello world');
        $record = array(
            'context' => array(
                'exception' => $exception,
            ),
        );

        $result = $processor($record);

        $expected = array(
            'message' => __FILE__ . "(40): Hello world\n[stack trace]",
            'context' => array(
                'exception' => $exception,
            ),
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_add_severity_for_errors()
    {
        $processor = new ExceptionToTextProcessor();
        Log::$debugBacktraceForTests = '[stack trace]';

        $exception = new \ErrorException('Hello world', 0, 1, 'file.php', 123);
        $record = array(
            'context' => array(
                'exception' => $exception,
            ),
        );

        $result = $processor($record);

        $expected = array(
            'message' => "file.php(123): Error - Hello world\n[stack trace]",
            'context' => array(
                'exception' => $exception,
            ),
        );

        $this->assertEquals($expected, $result);
    }
}

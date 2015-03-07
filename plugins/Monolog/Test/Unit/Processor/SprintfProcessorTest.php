<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Test\Unit\Processor;

use Piwik\Plugins\Monolog\Processor\SprintfProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\SprintfProcessor
 */
class SprintfProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_replace_placeholders()
    {
        $result = $this->process(array(
            'message' => 'Test %s and %s.',
            'context' => array('here', 'there'),
        ));

        $this->assertEquals('Test here and there.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_ignore_strings_without_placeholders()
    {
        $result = $this->process(array(
            'message' => 'Hello world!',
            'context' => array('foo', 'bar'),
        ));

        $this->assertEquals('Hello world!', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_serialize_arrays()
    {
        $result = $this->process(array(
            'message' => 'Error in the following modules: %s',
            'context' => array(array('import', 'export')),
        ));

        $this->assertEquals('Error in the following modules: ["import","export"]', $result['message']);
    }

    private function process($record)
    {
        $processor = new SprintfProcessor();
        return $processor($record);
    }
}

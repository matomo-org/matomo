<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Processor;

use Piwik\Plugins\Monolog\Processor\SprintfProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\SprintfProcessor
 */
class SprintfProcessorTest extends \PHPUnit\Framework\TestCase
{
    public function testItShouldReplacePlaceholders()
    {
        $result = $this->process(array(
            'message' => 'Test %s and %s.',
            'context' => array('here', 'there'),
        ));

        $this->assertEquals('Test here and there.', $result['message']);
    }

    public function testItShouldIgnoreStringsWithoutPlaceholders()
    {
        $result = $this->process(array(
            'message' => 'Hello world!',
            'context' => array('foo', 'bar'),
        ));

        $this->assertEquals('Hello world!', $result['message']);
    }

    public function testItShouldSerializeArrays()
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

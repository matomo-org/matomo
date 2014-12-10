<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Log\Processor;

use Piwik\Log\Processor\ClassNameProcessor;

/**
 * @group Core
 * @covers \Piwik\Log\Processor\ClassNameProcessor
 */
class ClassNameProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_append_classname_to_extra()
    {
        $result = $this->process(array(
            'extra' => array(
                'foo' => 'bar',
            ),
        ));

        $expected = array(
            'extra' => array(
                'foo' => 'bar',
                'class' => __CLASS__,
            ),
        );

        $this->assertEquals($expected, $result);
    }

    private function process($record)
    {
        $processor = new ClassNameProcessor();
        return $processor($record);
    }
}

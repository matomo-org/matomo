<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Processor;

use Piwik\Plugins\Monolog\Processor\ClassNameProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\ClassNameProcessor
 */
class ClassNameProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_should_append_classname_to_extra()
    {
        $processor = new ClassNameProcessor();

        $result = $processor(array(
            'extra' => array(
                'foo' => 'bar',
            ),
        ));

        $expected = array(
            'extra' => array(
                'foo' => 'bar',
                'class' => 'Monolog',
            ),
        );

        $this->assertEquals($expected, $result);
    }
}

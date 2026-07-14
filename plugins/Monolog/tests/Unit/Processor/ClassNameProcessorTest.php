<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Processor;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Piwik\Plugins\Monolog\Processor\ClassNameProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\ClassNameProcessor
 */
class ClassNameProcessorTest extends \PHPUnit\Framework\TestCase
{
    public function testItShouldAppendClassnameToExtra()
    {
        $processor = new ClassNameProcessor();

        $result = $processor(new LogRecord(
            new DateTimeImmutable(),
            'logger',
            Level::Debug,
            '',
            array(),
            array(
                'foo' => 'bar',
            )
        ));

        $expected = array(
            'foo' => 'bar',
            'class' => 'Monolog',
        );

        $this->assertEquals($expected, $result['extra']);
    }
}

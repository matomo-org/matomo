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
use PHPUnit\Framework\TestCase;
use Piwik\Common;
use Piwik\Plugins\Monolog\Processor\RequestIdProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\RequestIdProcessor
 */
class RequestIdProcessorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Common::$isCliMode = false;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Common::$isCliMode = true;
    }

    public function testItShouldAppendRequestIdToExtra()
    {
        $processor = new RequestIdProcessor();

        $result = $processor($this->makeRecord());

        $this->assertArrayHasKey('request_id', $result['extra']);
        self::assertIsString($result['extra']['request_id']);
        $this->assertNotEmpty($result['extra']['request_id']);
    }

    public function testRequestIdShouldStayTheSame()
    {
        $processor = new RequestIdProcessor();

        $result = $processor($this->makeRecord());
        $id1 = $result['extra']['request_id'];

        $result = $processor($this->makeRecord());
        $id2 = $result['extra']['request_id'];

        $this->assertEquals($id1, $id2);
    }

    private function makeRecord(): LogRecord
    {
        return new LogRecord(
            new DateTimeImmutable(),
            'logger',
            Level::Debug,
            '',
            array(),
            array()
        );
    }
}

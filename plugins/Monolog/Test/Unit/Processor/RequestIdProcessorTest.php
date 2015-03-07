<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Test\Unit\Processor;

use PHPUnit_Framework_TestCase;
use Piwik\Common;
use Piwik\Plugins\Monolog\Processor\RequestIdProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\RequestIdProcessor
 */
class RequestIdProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Common::$isCliMode = false;
    }

    public function tearDown()
    {
        parent::tearDown();
        Common::$isCliMode = true;
    }

    /**
     * @test
     */
    public function it_should_append_request_id_to_extra()
    {
        $processor = new RequestIdProcessor();

        $result = $processor(array());

        $this->assertArrayHasKey('request_id', $result['extra']);
        $this->assertInternalType('string', $result['extra']['request_id']);
        $this->assertNotEmpty($result['extra']['request_id']);
    }

    /**
     * @test
     */
    public function request_id_should_stay_the_same()
    {
        $processor = new RequestIdProcessor();

        $result = $processor(array());
        $id1 = $result['extra']['request_id'];

        $result = $processor(array());
        $id2 = $result['extra']['request_id'];

        $this->assertEquals($id1, $id2);
    }
}

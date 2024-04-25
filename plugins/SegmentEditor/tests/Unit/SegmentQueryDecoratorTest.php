<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Unit;

use Piwik\Plugins\SegmentEditor\SegmentQueryDecorator;
use Piwik\Plugins\SegmentEditor\Services\StoredSegmentService;
use Piwik\Segment\SegmentExpression;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;

/**
 * @group SegmentEditor
 * @group SegmentEditor_Unit
 */
class SegmentQueryDecoratorTest extends \PHPUnit\Framework\TestCase
{
    public static $storedSegments = array(
        array('definition' => 'countryCode==abc', 'idsegment' => 1),
        array('definition' => 'region!=FL', 'idsegment' => 2),
        array('definition' => 'browserCode==def;visitCount>2', 'idsegment' => 3),
        array('definition' => 'region!=FL', 'idsegment' => 4),
    );

    /**
     * @var SegmentQueryDecorator
     */
    private $decorator;

    public function setUp(): void
    {
        parent::setUp();

        /** @var StoredSegmentService $service */
        $service = $this->getMockSegmentEditorService();
        $logTables = new LogTablesProvider();
        $this->decorator = new SegmentQueryDecorator($service, $logTables);
    }

    public function testGetSelectQueryStringDoesNotDecorateSqlWhenNoSegmentUsed()
    {
        $expression = new SegmentExpression('');
        $expression->parseSubExpressions();

        $query = $this->decorator->getSelectQueryString($expression, '*', 'log_visit', '', array(), '', '', '');

        $this->assertStringStartsNotWith('/* idSegments', $query['sql']);
    }

    public function testGetSelectQueryStringDoesNotDecorateSqlWhenNoSegmentMatchesUsedSegment()
    {
        $expression = new SegmentExpression('referrerName==ooga');
        $expression->parseSubExpressions();

        $query = $this->decorator->getSelectQueryString($expression, '*', 'log_visit', '', array(), '', '', '');

        $this->assertStringStartsNotWith('/* idSegments', $query['sql']);
    }

    public function testGetSelectQueryStringDecoratesSqlWhenOneSegmentMatchesUsedSegment()
    {
        $expression = new SegmentExpression('browserCode==def;visitCount>2');
        $expression->parseSubExpressions();

        $query = $this->decorator->getSelectQueryString($expression, '*', 'log_visit', '', array(), '', '', '');

        $this->assertStringStartsWith('SELECT /* idSegments = [3] */', $query['sql']);
        $this->assertEquals(1, substr_count($query['sql'], 'SELECT'));
    }

    public function testGetSelectQueryStringDecoratesSqlWhenMultipleStoredSegmentsMatchUsedSegment()
    {
        $expression = new SegmentExpression('region!=FL');
        $expression->parseSubExpressions();

        $query = $this->decorator->getSelectQueryString($expression, '*', 'log_visit', '', array(), '', '', '');

        $this->assertStringStartsWith('SELECT /* idSegments = [2, 4] */', $query['sql']);
        $this->assertEquals(1, substr_count($query['sql'], 'SELECT'));
    }

    private function getMockSegmentEditorService()
    {
        $mock = $this->getMockBuilder('Piwik\Plugins\SegmentEditor\Services\StoredSegmentService')
            ->setMethods(['getAllSegmentsAndIgnoreVisibility'])
            ->setConstructorArgs([])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())->method('getAllSegmentsAndIgnoreVisibility')->willReturn(self::$storedSegments);
        return $mock;
    }
}

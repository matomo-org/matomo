<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Segment;

/**
 * @group SegmentTest
 * @group Segment
 */
class SegmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getDefinitionsAndOperands
     */
    public function test_containsOperand($definition, $operand, $result)
    {
        $this->assertEquals($result, Segment::containsOperand($definition, $operand));
    }

    public function getDefinitionsAndOperands()
    {
        return [
            ['visitorId==123', 'visitorId', true],
            ['visitorId==123', 'actions', false],
            ['visitorId%3D%3D123', 'actions', false],
            ['visitorId%3D%3D123', 'visitorId', true],
            ['actions%3D%3D2,visitorId%3D%3D1234567890123456;actionType%3D%3Dcontents', 'actions', true],
            ['actions%3D%3D2,visitorId%3D%3D1234567890123456;actionType%3D%3Dcontents', 'pageUrl', false],
            ['actions==2,visitorId==1234567890123456;actionType==contents', 'actions', true],
            ['actions==2,visitorId==1234567890123456;actionType==contents', 'pageUrl', false],
        ];
    }
}
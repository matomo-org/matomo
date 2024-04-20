<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\SegmentEditor\SegmentQueryDecorator;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentEditor
 * @group SegmentEditor_Integration
 */
class SegmentQueryDecoratorTest extends IntegrationTestCase
{
    /**
     * @var SegmentQueryDecorator
     */
    private $segmentQueryDecorator;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2011-01-01');
        Fixture::createWebsite('2011-01-01');
        Fixture::createWebsite('2011-01-01');

        $this->segmentQueryDecorator = self::$fixture->piwikEnvironment->getContainer()->get(
            'Piwik\Plugins\SegmentEditor\SegmentQueryDecorator'
        );

        Rules::setBrowserTriggerArchiving(false);

        /** @var API $segmentEditorApi */
        $segmentEditorApi = self::$fixture->piwikEnvironment->getContainer()->get(
            'Piwik\Plugins\SegmentEditor\API'
        );
        $segmentEditorApi->add('segment 1', 'visitCount<2', $idSite = false, $autoArchive = true);
        $segmentEditorApi->add('segment 2', 'countryCode==fr', $idSite = false, $autoArchive = true);
        $segmentEditorApi->add('segment 3', 'visitCount<2', 1, $autoArchive = true);
        $segmentEditorApi->add('segment 4', 'visitCount<2', 2, $autoArchive = true);

        // test that segments w/ auto archive == false are included
        $segmentEditorApi->add('segment 5', 'visitCount<2', 3, $autoArchive = false);
        $segmentEditorApi->add('segment 6', 'countryCode!=fr', 3, $autoArchive = false);

        Rules::setBrowserTriggerArchiving(true);
    }

    /**
     * @dataProvider getTestDataForSegmentSqlTest
     */
    public function test_SegmentSql_IsCorrectlyDecoratedWithIdSegment($segment, $triggerValue, $expectedPrefix)
    {
        if (!empty($triggerValue)) {
            $_GET['trigger'] = $triggerValue;
        }

        $segment = new Segment($segment, array());

        $query = $segment->getSelectQuery('*', 'log_visit');
        $sql = $query['sql'];

        if (empty($expectedPrefix)) {
            $this->assertStringStartsNotWith("/* idSegments", $sql);
        } else {
            $this->assertStringStartsWith($expectedPrefix, $sql);
            $this->assertEquals(1, substr_count($sql, 'SELECT'));
        }
    }

    public function getTestDataForSegmentSqlTest()
    {
        return array(
            array('countryCode==fr', null, 'SELECT /* idSegments = [2] */'),
            array('visitCount<2', null, 'SELECT /* idSegments = [1, 3, 4, 5] */'),
            array('', null, null),
            array('countryCode!=fr', null, 'SELECT /* idSegments = [6] */'),

            array('', 'archivephp', 'SELECT /* trigger = CronArchive */'),
            array('countryCode!=fr', 'archivephp', 'SELECT /* trigger = CronArchive, idSegments = [6] */'),

            array('', 'garbage', null),
            array('countryCode!=fr', 'garbage', 'SELECT /* idSegments = [6] */'),
        );
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Period;

/**
 * @group ArchiveProcessor
 * @group ArchiveProcessorParameters
 */
class ParametersTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2012-02-01 00:00:00');
    }

    /**
     * @dataProvider getTestDataForIsDayArchive
     */
    public function test_isDayArchive_CorrectlyDetectsDayArchives($expected, $dateStr, $periodStr)
    {
        $period = Period\Factory::build($periodStr, $dateStr);
        $params = new Parameters(new Site(1), $period, new Segment('', [1]));
        $actual = $params->isDayArchive();
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForIsDayArchive()
    {
        return [
            [true, '2012-02-02', 'day'],
            [false, '2013-03-04', 'week'],
            [false, '2013-03-04', 'month'],
            [false, '2013-03-04', 'year'],
            [true, '2012-02-02,2012-02-02', 'range'],
            [false, '2012-02-02,2012-02-03', 'range'],
            [false, '2012-02-02,2012-02-05', 'range'],
        ];
    }
}

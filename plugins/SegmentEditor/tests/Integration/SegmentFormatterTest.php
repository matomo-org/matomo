<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\Plugins\SegmentEditor\SegmentFormatter;
use Piwik\Segment\SegmentsList;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentFormatterTest
 * @group SegmentFormatter
 * @group SegmentEditor
 * @group Plugins
 */
class SegmentFormatterTest extends IntegrationTestCase
{
    /**
     * @var SegmentFormatter
     */
    private $formatter;

    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        $this->idSite = Fixture::createWebsite('2012-01-01 00:00:00');
        $this->formatter   = new SegmentFormatter(SegmentsList::get());
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
    }

    public function test_getHumanReadable_noSegmentGiven_ShouldReturnDefaultSegment()
    {
        $readable = $this->formatter->getHumanReadable($segment = '', $this->idSite);
        $this->assertSame('All visits', $readable);
    }

    public function test_getHumanReadable_ShouldTranslateAMetric()
    {
        $readable = $this->formatter->getHumanReadable($segment = 'visitCount>5', $this->idSite);
        $this->assertSame('Number of visits greater than "5"', $readable);

        $readable = $this->formatter->getHumanReadable($segment = 'visitCount==5', $this->idSite);
        $this->assertSame('Number of visits equals "5"', $readable);
    }

    public function test_getHumanReadable_ShouldTranslateADimension()
    {
        $readable = $this->formatter->getHumanReadable($segment = 'resolution=@1024', $this->idSite);
        $this->assertSame('Resolution contains "1024"', $readable);

        $readable = $this->formatter->getHumanReadable($segment = 'resolution==1024x768', $this->idSite);
        $this->assertSame('Resolution is "1024x768"', $readable);
    }

    public function test_getHumanReadable_ShouldCombineMultipleSegmentDefinitionsWithBooleanOperator()
    {
        $readable = $this->formatter->getHumanReadable($segment = 'browserVersion!=1.0;browserEngine=$Trident', $this->idSite);
        $this->assertSame('Browser version is not "1.0" and Browser engine ends with "Trident"', $readable);

        $readable = $this->formatter->getHumanReadable($segment = 'browserVersion!=1.0,browserEngine=$Trident', $this->idSite);
        $this->assertSame('Browser version is not "1.0" or Browser engine ends with "Trident"', $readable);
    }

    public function test_getHumanReadable_ShouldHandleAMissingValue()
    {
        $readable = $this->formatter->getHumanReadable($segment = 'browserVersion==', $this->idSite);
        $this->assertSame('Browser version is null or empty', $readable);

        $readable = $this->formatter->getHumanReadable($segment = 'browserVersion!=', $this->idSite);
        $this->assertSame('Browser version is not null nor empty', $readable);
    }

    public function test_getHumanReadable_ShouldHandleAUrlDecodedSegment()
    {
        $readable = $this->formatter->getHumanReadable($segment = 'pageUrl%3D%40piwik%2CvisitId!%3D1', $this->idSite);
        $this->assertSame('Page URL contains "piwik" or Visit ID is not "1"', $readable);
    }

    public function test_getHumanReadable_ShouldThrowAnException_IfTheGivenSegmentNameDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The segment \'noTexisTinG\' does not exist');

        $this->formatter->getHumanReadable($segment = 'noTexisTinG==1.0', $this->idSite);
    }

    public function test_getHumanReadable_ShouldThrowAnException_IfSegmentCannotBeParsedBecauseOfInvalidFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The segment condition \'pageUrl=!1.0\' is not valid.');

        $invalidOperator = '=!';
        $this->formatter->getHumanReadable($segment = 'pageUrl' . $invalidOperator . '1.0', $this->idSite);
    }
}

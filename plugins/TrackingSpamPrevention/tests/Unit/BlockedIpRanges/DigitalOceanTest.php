<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Unit\BlockedIpRanges;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\DigitalOcean;

/**
 * @group TrackingSpamPrevention
 * @group BlockedIpRangesTest
 * @group Plugins
 */
class DigitalOceanTest extends TestCase
{
    /**
     * @var DigitalOcean
     */
    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new DigitalOcean();
    }

    public function testParseRangesReadsTheRangeFromTheFirstColumnOfEveryLine()
    {
        $csv = $this->makeCsv([
            '5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH',
            '165.22.0.0/20,US,US-NJ,North Bergen,07047',
            '2400:6180:0:d0::/64,SG,SG-05,Singapore,627753',
        ]);

        $this->assertSame([
            '5.101.96.0/21',
            '165.22.0.0/20',
            '2400:6180:0:d0::/64',
        ], $this->provider->parseRanges($csv));
    }

    /**
     * @dataProvider getLineEndings
     */
    public function testParseRangesSupportsAnyLineEnding($lineEnding)
    {
        $csv = implode($lineEnding, [
            '5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH',
            '165.22.0.0/20,US,US-NJ,North Bergen,07047',
        ]);

        $this->assertSame(['5.101.96.0/21', '165.22.0.0/20'], $this->provider->parseRanges($csv));
    }

    public function getLineEndings()
    {
        return [["\n"], ["\r\n"], ["\r"]];
    }

    public function testParseRangesSkipsBlankLines()
    {
        $csv = $this->makeCsv([
            '',
            '5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH',
            '   ',
            '165.22.0.0/20,US,US-NJ,North Bergen,07047',
            '',
        ]);

        $this->assertSame(['5.101.96.0/21', '165.22.0.0/20'], $this->provider->parseRanges($csv));
    }

    /**
     * @dataProvider getEmptyResponses
     */
    public function testParseRangesEmptyResponse($csv)
    {
        $this->assertSame([], $this->provider->parseRanges($csv));
    }

    public function getEmptyResponses()
    {
        return [[''], [' '], ["\n"], ["\n \n"], ["\r\n\r\n"]];
    }

    public function testParseRangesErrorPageServedWithStatus200()
    {
        $csv = $this->makeCsv(['<html>', '<body>Something went wrong</body>', '</html>']);

        $this->assertSame([], $this->provider->parseRanges($csv));
    }

    public function testParseRangesIgnoresAHeaderRowShouldOneBeAdded()
    {
        $csv = $this->makeCsv([
            'range,country,region,city,postcode',
            '5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH',
        ]);

        $this->assertSame(['5.101.96.0/21'], $this->provider->parseRanges($csv));
    }

    public function testParseRangesHandlesQuotedFieldContainingASeparator()
    {
        $csv = $this->makeCsv(['5.101.96.0/21,US,US-CA,"Santa Clara, CA",95054']);

        $this->assertSame(['5.101.96.0/21'], $this->provider->parseRanges($csv));
    }

    public function testParseRangesDropsInvalidRanges()
    {
        $csv = $this->makeCsv([
            'notanip,NL,NL-NH,Amsterdam,1098 XH',
            '5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH',
            '999.999.0.0/21,NL,NL-NH,Amsterdam,1098 XH',
            '1.2.3.4/99,NL,NL-NH,Amsterdam,1098 XH',
            '1.2.3.4/abc,NL,NL-NH,Amsterdam,1098 XH',
            '165.22.0.0/20,US,US-NJ,North Bergen,07047',
        ]);

        $this->assertSame(['5.101.96.0/21', '165.22.0.0/20'], $this->provider->parseRanges($csv));
    }

    public function testParseRangesNormalisesSingleIpsAndWildcards()
    {
        $csv = $this->makeCsv([
            '1.2.3.4,NL,NL-NH,Amsterdam,1098 XH',
            '1.2.3.*,NL,NL-NH,Amsterdam,1098 XH',
            ' 165.22.0.0/20,US,US-NJ,North Bergen,07047',
        ]);

        $this->assertSame(['1.2.3.4/32', '1.2.3.0/24', '165.22.0.0/20'], $this->provider->parseRanges($csv));
    }

    public function testParseRangesStripsAUtf8ByteOrderMark()
    {
        $csv = "\xEF\xBB\xBF" . $this->makeCsv(['5.101.96.0/21,NL,NL-NH,Amsterdam,1098 XH']);

        $this->assertSame(['5.101.96.0/21'], $this->provider->parseRanges($csv));
    }

    private function makeCsv(array $lines)
    {
        // the response ends with a trailing new line
        return implode("\n", $lines) . "\n";
    }
}

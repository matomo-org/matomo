<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Request;
use Piwik\Date;
use Piwik\Tests\Fixtures\OneVisitWithReferrerUrlThatIsNoXmlName;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group TrackedDataInXmlExportTest
 */
class TrackedDataInXmlExportTest extends SystemTestCase
{
    /**
     * @var OneVisitWithReferrerUrlThatIsNoXmlName
     */
    public static $fixture = null;

    public function testReportContentIsNotUsedAsMarkupInXmlResponse()
    {
        $response = $this->getPivotedReferrersReportAsBulkXml();

        self::assertStringNotContainsString('<injected', $response);
        $this->assertValidXML($response);

        // the path is still part of the response, escaped. asserted so that the test cannot pass by
        // the report no longer holding the path at all
        self::assertStringContainsString('&lt;injected/&gt;', $response);
    }

    /**
     * Requests a report through the reporting API in a way that has the tracked path reach the XML
     * renderer where a name is expected, rather than as a value of a row.
     */
    private function getPivotedReferrersReportAsBulkXml(): string
    {
        $nestedRequest = 'method=Referrers.getWebsites'
            . '&idSite=' . self::$fixture->idSite
            . '&period=day'
            . '&date=' . Date::factory(self::$fixture->dateTime)->toString()
            . '&pivotBy=Referrers.WebsitePage'
            . '&pivotByColumn=nb_visits'
            . '&pivotByColumnLimit=-1'
            . '&disable_queued_filters=1'
            . '&filter_limit=-1';

        $request = new Request([
            'module' => 'API',
            'method' => 'API.getBulkRequest',
            'format' => 'xml',
            'urls'   => [$nestedRequest],
        ]);

        return $request->process();
    }
}

TrackedDataInXmlExportTest::$fixture = new OneVisitWithReferrerUrlThatIsNoXmlName();

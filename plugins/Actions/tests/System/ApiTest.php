<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ApiTest extends IntegrationTestCase
{
    public function test_actionUrlSegmentValueIsProperlyEncoded_inActionsReports()
    {
        $url = 'http://example+site.org/a+b/index.html';

        $idSite = Fixture::createWebsite('2012-03-04 00:00:00');
        $t = Fixture::getTracker($idSite, '2015-03-04 03:24:00');
        $t->setUrl($url);
        Fixture::checkResponse($t->doTrackPageView('a page+view'));

        /** @var DataTable $urls */
        $urls = Request::processRequest('Actions.getPageUrls', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => '2015-03-04',
            'flat' => '1',
        ]);

        $this->assertEquals(1, $urls->getRowsCount());

        $urlSegment = $urls->getFirstRow()->getMetadata('segment');

        /** @var DataTable $urlsWithSegment */
        $urlsWithSegment = Request::processRequest('Actions.getPageUrls', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => '2015-03-04',
            'segment' => $urlSegment,
            'flat' => '1',
        ]);

        $this->assertEquals(1, $urlsWithSegment->getRowsCount());

        // NOTE: the label here is incorrect due to SafeDecodeLabel. this is a known issue, but changing it would
        // break BC elsewhere
        $this->assertEquals('/a b/index.html', $urlsWithSegment->getFirstRow()->getColumn('label'));

        $pages = Request::processRequest('Actions.getPageTitles', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => '2015-03-04',
            'flat' => '1',
        ]);

        $this->assertEquals(1, $pages->getRowsCount());

        $pageSegment = $pages->getFirstRow()->getMetadata('segment');

        /** @var DataTable $pagesWithSegment */
        $pagesWithSegment = Request::processRequest('Actions.getPageTitles', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => '2015-03-04',
            'segment' => $pageSegment,
            'flat' => '1',
        ]);
        $this->assertEquals(1, $pagesWithSegment->getRowsCount());

        // NOTE: the label here is incorrect due to SafeDecodeLabel. this is a known issue, but changing it would
        // break BC elsewhere
        $this->assertEquals('a page view', $pagesWithSegment->getFirstRow()->getColumn('label'));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}

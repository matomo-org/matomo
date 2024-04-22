<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\System;

use Piwik\API\Request;
use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ApiInvalidParameterTypeTest
 */
class ApiInvalidParameterTypeTest extends IntegrationTestCase
{
    public function testActionUrlSegmentValueIsProperlyEncodedInActionsReports()
    {
        $url = 'http://example+site.org/a+b/index.html';

        $idSite = Fixture::createWebsite('2012-03-04 00:00:00');
        $t = Fixture::getTracker($idSite, '2015-03-04 03:24:00');
        $t->setUrl($url);
        Fixture::checkResponse($t->doTrackPageView('a page+view'));

        // Attempt to call an API method with a string idSubtable value
        try {

            /** @var DataTable $urls */
            $urls = Request::processRequest('Actions.getPageUrls', [
                'idSite' => $idSite,
                'idSubtable' => 'undefined', // This is invalid
                'period' => 'day',
                'date' => '2015-03-04',
                'flat' => '1',
            ]);

            $this->fail('Exception was not thrown');
        } catch (\Throwable $e) {
            $this->assertStringStartsWith('idSubtable needs to be a number', $e->getMessage());
        }

        // Attempt to call the same API method with a numeric idSubtable value
        /** @var DataTable $urls */
        $urls = Request::processRequest('Actions.getPageUrls', [
            'idSite' => $idSite,
            'idSubtable' => 1, // valid
            'period' => 'day',
            'date' => '2015-03-04',
            'flat' => '1',
        ]);

        $this->assertEquals(1, $urls->getRowsCount());

        // Attempt to call the same API method with the 'all' idSubtable value
        /** @var DataTable $urls */
        $urls = Request::processRequest('Actions.getPageUrls', [
            'idSite' => $idSite,
            'idSubtable' => Archive::ID_SUBTABLE_LOAD_ALL_SUBTABLES, // valid
            'period' => 'day',
            'date' => '2015-03-04',
            'flat' => '1',
        ]);

        $this->assertEquals(1, $urls->getRowsCount());
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}

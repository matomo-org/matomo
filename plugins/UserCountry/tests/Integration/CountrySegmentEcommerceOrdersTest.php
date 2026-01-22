<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Integration;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UserCountry
 * @group Ecommerce
 * @group Plugins
 */
class CountrySegmentEcommerceOrdersTest extends IntegrationTestCase
{
    public function testSegmentedCountryMatchesUnsegmentedCountryRowForEcommerceOrders()
    {
        $date = '2015-03-04';
        $idSite = Fixture::createWebsite($date . ' 00:00:00', $ecommerce = 1);

        $this->trackEcommerceOrder($idSite, $date . ' 00:01:00', 'nz', '1001', 'visit-nz-1');
        $this->trackEcommerceOrder($idSite, $date . ' 00:02:00', 'nz', '1002', 'visit-nz-2');
        $this->trackEcommerceOrder($idSite, $date . ' 00:03:00', 'us', '2001', 'visit-us-1');

        /** @var DataTable $allCountries */
        $allCountries = Request::processRequest('UserCountry.getCountry', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => $date,
            'flat' => '1',
            'filter_limit' => -1,
        ]);

        $countryCode = 'nz';
        $nzRowAll = $this->findCountryRowByCode($allCountries, $countryCode);
        $this->assertNotFalse($nzRowAll, 'Expected unsegmented UserCountry.getCountry response to include nz row.');

        /** @var DataTable $nzOnly */
        $nzOnly = Request::processRequest('UserCountry.getCountry', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => $date,
            'segment' => 'countryCode==' . $countryCode,
            'flat' => '1',
            'filter_limit' => -1,
        ]);

        $this->assertEquals(1, $nzOnly->getRowsCount(), 'Expected segmented response to include exactly one country row.');
        $nzRowSegmented = $this->findCountryRowByCode($nzOnly, $countryCode);
        $this->assertNotFalse($nzRowSegmented, 'Expected segmented response to include nz row.');

        $this->assertEquals(
            $nzRowAll->getColumn('nb_visits'),
            $nzRowSegmented->getColumn('nb_visits'),
            'Expected nz nb_visits to match between unsegmented and segmented results.'
        );
        $this->assertEquals(
            $nzRowAll->getColumn('nb_actions'),
            $nzRowSegmented->getColumn('nb_actions'),
            'Expected nz nb_actions to match between unsegmented and segmented results.'
        );
    }

    private function trackEcommerceOrder($idSite, $dateTime, $countryCode, $orderId, $visitorId)
    {
        $t = Fixture::getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setCountry($countryCode);
        $t->setVisitorId(substr(md5($visitorId), 0, $t::LENGTH_VISITOR_ID));
        $t->setUrl('http://example.org/product-page');
        $t->addEcommerceItem('SKU-' . $orderId, 'Product ' . $orderId, 'Category', 100, 1);
        Fixture::checkResponse($t->doTrackEcommerceOrder($orderId, 111.11, 100, 11));
    }

    private function findCountryRowByCode(DataTable $table, $countryCode)
    {
        foreach ($table->getRows() as $row) {
            if ($row->getMetadata('code') === $countryCode) {
                return $row;
            }
        }

        return false;
    }
}

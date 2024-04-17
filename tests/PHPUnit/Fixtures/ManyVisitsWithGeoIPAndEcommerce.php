<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

class ManyVisitsWithGeoIPAndEcommerce extends ManyVisitsWithGeoIP
{
    /**
     * Insert a new visit into the database.
     * @param \MatomoTracker $t          The tracker to record the visits on
     * @param int $fixtureCounter       Number of times this fixture has been run
     * @param int $visitorCounter       Visitor counter within this execution of the fixture
     * @param boolean $doBulk           Should this visit be left for bulk insert later, or processed now?
     * @param array $params             Other params as required to set up the visit
     */
    protected function trackVisit(\MatomoTracker $t, $fixtureCounter, $visitorCounter, $doBulk, array $params)
    {
        // Add some ecommerce views
        if (($visitorCounter % 3) == 1) {
            $t->setEcommerceView('Custom SKU', 'MyName', ['Category1', 'Category2', 'Category3', 'Category' . $visitorCounter], 17.4);
        }

        parent::trackVisit($t, $fixtureCounter, $visitorCounter, $doBulk, $params);

        // Add a few ecommerce orders
        if (($visitorCounter % 3) == 0) {
            $orderId = $fixtureCounter * 1000 + $visitorCounter + 1;

            $t->addEcommerceItem('ABCD1234', 'My Product', 'My Category', 100, 1);
            $r = $t->doTrackEcommerceOrder($orderId, 111.11, 100, 11);
            if (!$doBulk) {
                self::checkResponse($r);
            }
        }
    }
}

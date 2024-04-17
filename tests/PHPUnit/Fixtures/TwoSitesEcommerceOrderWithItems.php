<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds two sites and tracks some visits with ecommerce orders.
 */
class TwoSitesEcommerceOrderWithItems extends Fixture
{
    public $dateTime = '2011-04-05 00:11:42';
    public $idSite = 1;
    public $idSite2 = 2;
    public $idGoalStandard = 1;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        self::setUpScheduledReports($this->idSite);

        $this->trackVisitsSite1($url = 'http://example.org/index.htm');
        $this->trackVisitsSite2($url = 'http://example-site2.com/index.htm');
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($this->idSite)) {
            $this->idSite = self::createWebsite($this->dateTime, $ecommerce = 1, 'A very long name for a very tiny test site, that also reaches the 90 character limit fully');
        }

        if (!self::siteCreated($this->idSite2)) {
            $this->idSite2 = self::createWebsite($this->dateTime);
        }

        if (!self::goalExists($this->idSite, $this->idGoalStandard)) {
            API::getInstance()->addGoal(
                $this->idSite,
                'title match, triggered ONCE',
                'title',
                'incredible',
                'contains',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = true
            );
        }
    }

    protected function trackVisitsSite1($url, $orderId = '937nsjusu 3894', $orderId2 = '1037nsjusu4s3894', $orderId3 = '666', $orderId4 = '777')
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        // VISIT NO 1
        $t->setUrl($url);
        $category = 'Electronics & Cameras';
        $price = 1111.11111;

        // VIEW product page
        $t->setEcommerceView('SKU2', 'PRODUCT name', $category, $price);
        $t->setCustomVariable(5, 'VisitorType', 'NewLoggedOut', 'visit');
        $t->setCustomVariable(4, 'ValueIsZero', '0', 'visit');
        self::assertEquals(array('VisitorType', 'NewLoggedOut'), $t->getCustomVariable(5, 'visit'));

        // this is also a goal conversion (visitConvertedGoalId==1)
        self::checkResponse($t->doTrackPageView('incredible title!'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category, $price = 666);
        self::checkResponse($t->doTrackPageView('Another Product page'));

        // Note: here testing to pass a timestamp to the tracking API rather than the datetime string
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getTimestampUTC());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $cat = '', $price = 888);
        self::checkResponse($t->doTrackPageView('Another Product page with no category'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $categories = ['Multiple Category 1', '', 0, 'Multiple Category 2', 'Electronics & Cameras', 'Multiple Category 4', 'Multiple Category 5', 'SHOULD NOT BE REPORTEDSSSSSSSSSSSSSSssssssssssssssssssssssssssstttttttttttttttttttttttuuuu!']);
        self::checkResponse($t->doTrackPageView('Another Product page with multiple categories'));

        // VISIT NO 2

        // Fake the returning visit cookie
        // TODO: can't do this w/ idvc, should be fine for a test
        $t->setBrowserLanguage('pl');

        // VIEW category page
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.6)->getDatetime());
        $t->setEcommerceView('', '', $category);
        self::checkResponse($t->doTrackPageView('Looking at ' . $category . ' page with a page level custom variable'));

        // VIEW category page again
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.7)->getDatetime());
        $t->setEcommerceView('', '', $category);
        self::checkResponse($t->doTrackPageView('Looking at ' . $category . ' page again'));

        // VIEW product page
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.8)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 666);
        self::checkResponse($t->doTrackPageView('Looking at product page'));

        // ADD TO CART
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.9)->getDatetime());
        $t->setCustomVariable(3, 'VisitorName', 'Great name!', 'visit');
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 500, $quantity = 1);
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed REMOVED', $name = 'PRODUCT name REMOVED', $category = 'Electronics & Cameras REMOVED', $price = 300, $quantity = 1);
        $t->addEcommerceItem($sku = 'SKU WILL BE DELETED', $name = 'BLABLA DELETED', $category = '', $price = 5000000, $quantity = 20);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1300));

        // REMOVE FROM CART
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.95)->getDatetime());
        $t->setCustomVariable(3, 'VisitorName', 'Great name!', 'visit');
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 500, $quantity = 1);
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'SKU WILL BE DELETED', $name = 'BLABLA DELETED', $category = '', $price = 5000000, $quantity = 20);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));

        // ORDER NO 1
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $categories, $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'ANOTHER SKU HERE', $name = 'PRODUCT name BIS', $category = '', $price = 100, $quantity = 6);

        self::checkResponse($t->doTrackEcommerceOrder($orderId, $grandTotal = 1111.11, $subTotal = 1000, $tax = 111, $shipping = 0.11, $discount = 666));

        // ORDER NO 2
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.1)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR', $category = 'Electronics & Cameras', $price = 1500, $quantity = 1);
        // Product bought with empty category
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', '', $price = 11.22, $quantity = 1);

        // test to delete all custom vars, they should be copied from visits
        // This is a frequent use case: ecommerce shops tracking the order from backoffice
        // without passing the custom variable 1st party cookie along since it's not known by back office
        $visitorCustomVarSave = $t->visitorCustomVar;
        $t->visitorCustomVar = false;

        self::checkResponse($t->doTrackEcommerceOrder($orderId2, $grandTotal = 2000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
        $t->visitorCustomVar = $visitorCustomVarSave;

        // ORDER SHOULD DEDUPE
        // Refresh the page with the receipt for the second order, should be ignored
        // we test that both the order, and the products, are not updated on subsequent "Receipt" views
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.2)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR NOT!', $category = 'Electronics & Cameras NOT!', $price = 15000000000, $quantity = 10000);
        self::checkTrackingFailureResponse($t->doTrackEcommerceOrder($orderId2, $grandTotal = 20000000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));

        // Leave with an opened cart
        // No category
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.3)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart', $category = '', $price = 500.11111112, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));

        // Record the same visit leaving twice an abandoned cart
        foreach (array(0, 5, 24) as $offsetHour) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($offsetHour + 2.4)->getDatetime());
            // Also recording an order the day after (purposefully using old order ID, it should be ignored by the tracker since it was used in a previous visit)
            if ($offsetHour >= 24) {
                $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR', $category = 'Electronics & Cameras', $price = 1500, $quantity = 1);
                self::checkTrackingFailureResponse($t->doTrackEcommerceOrder($orderId2, $grandTotal = 20000000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
            }

            // VIEW PRODUCT PAGES
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($offsetHour + 2.5)->getDatetime());
            $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart', $category = '', $price = 999);
            self::checkResponse($t->doTrackPageView("View product left in cart"));

            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($offsetHour + 2.55)->getDatetime());
            $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart', $category = '', $price = 333);
            self::checkResponse($t->doTrackPageView("View product left in cart"));

            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($offsetHour + 2.6)->getDatetime());
            $t->setEcommerceView($sku = 'SKU IN ABANDONED CART TWO', $name = 'PRODUCT TWO LEFT in cart', $category = ['Category TWO LEFT in cart', 'second category']);
            self::checkResponse($t->doTrackPageView("View product left in cart"));

            // ABANDONED CART
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($offsetHour + 2.7)->getDatetime());
            $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart', $category = '', $price = 500.11111112, $quantity = 1);
            $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART TWO', $name = 'PRODUCT TWO LEFT in cart', $category = ['Category TWO LEFT in cart', 'second category'], $price = 1000, $quantity = 2);
            $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart', $category = 'Electronics & Cameras', $price = 10, $quantity = 1);
            self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 2510.11111112));
        }

        // One more Ecommerce order to check weekly archiving works fine on orders
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(30.7)->getDatetime());
        $t->addEcommerceItem($sku = 'TRIPOD SKU', $name = 'TRIPOD - bought day after', $category = 'Tools', $price = 100, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceOrder($orderId3, $grandTotal = 240, $subTotal = 200, $tax = 20, $shipping = 20, $discount = 20));

        // One more Ecommerce order, without any product in it, because we still track orders without products
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(30.8)->getDatetime());
        self::checkResponse($t->doTrackEcommerceOrder($orderId4, $grandTotal = 10000));

        return array($defaultInit, $t, $category, $price, $sku, $name, $quantity, $grandTotal, $orderId);
    }

    /**
     * @param $this->dateTime
     */
    protected function trackVisitsSite2($url)
    {
        $t = self::getTracker($this->idSite2, $this->dateTime, $defaultInit = true);

        // Same page name as on website1, different domain (for MetaSites test)
        $t->setUrl($url);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1)->getDatetime());
        $t->setCustomVariable(1, "cvar Name 1", "cvar Value 1");
        self::checkResponse($t->doTrackPageView('one page visit'));

        // testing the same order in a different website should record
        $t = self::getTracker($this->idSite2, $this->dateTime, $defaultInit = true);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(30.9)->getDatetime());
        $t->addEcommerceItem($sku = 'TRIPOD SKU', $name = 'TRIPOD - bought day after', $category = 'Tools', $price = 100, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '777', $grandTotal = 250));
    }
}

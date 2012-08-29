<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * Tests API methods after ecommerce orders are tracked.
 */
class Test_Piwik_Integration_EcommerceOrderWithItems extends IntegrationTestCase
{
    protected static $dateTime       = '2011-04-05 00:11:42';
    protected static $idSite         = 1;
    protected static $idSite2        = 2;
    protected static $idGoalStandard = 1;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            self::setUpWebsitesAndGoals();
			self::setUpScheduledReports(self::$idSite);
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        EcommerceOrderWithItems
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $dayApi = array('VisitsSummary.get', 'VisitTime', 'CustomVariables.getCustomVariables',
                        'Live.getLastVisitsDetails', 'UserCountry', 'API.getProcessedReport', 'Goals.get',
                        'Goals.getConversions', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        $goalWeekApi = array('Goals.get', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        $goalItemApi = array('Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        $processedReportApi = array('API.getProcessedReport');

        // Normal standard goal
        return array_merge(array(

            // day tests
            array($dayApi, array('idSite' => self::$idSite, 'date' => self::$dateTime, 'periods' => array('day'), 'otherRequestParameters' => array('_leavePiwikCoreVariables' => 1))),

            // goals API week tests
            array($goalWeekApi, array('idSite' => self::$idSite, 'date' => self::$dateTime, 'periods' => array('week'))),

            // abandoned carts tests
            array($goalItemApi, array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                      'periods'    => array('day', 'week'), 'abandonedCarts' => 1,
                                      'testSuffix' => '_AbandonedCarts')),

            // multiple periods tests
            array($goalItemApi, array('idSite'       => self::$idSite, 'date' => self::$dateTime, 'periods' => array('day'),
                                      'setDateLastN' => true, 'testSuffix' => 'multipleDates')),

            // multiple periods & multiple websites tests
            array($goalItemApi, array('idSite'     => sprintf("%u,%u", self::$idSite, self::$idSite2), 'date' => self::$dateTime,
                                      'periods'    => array('day'), 'setDateLastN' => true,
                                      'testSuffix' => 'multipleDates_andMultipleWebsites')),

            // test metadata products
            array($processedReportApi, array('idSite'    => self::$idSite, 'date' => self::$dateTime,
                                             'periods'   => array('day'), 'apiModule' => 'Goals',
                                             'apiAction' => 'getItemsSku', 'testSuffix' => '_Metadata_ItemsSku')),
            array($processedReportApi, array('idSite'    => self::$idSite, 'date' => self::$dateTime,
                                             'periods'   => array('day'), 'apiModule' => 'Goals',
                                             'apiAction' => 'getItemsCategory', 'testSuffix' => '_Metadata_ItemsCategory')),

            // test metadata Goals.get for Ecommerce orders & Carts
            array($processedReportApi, array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                             'idGoal'     => Piwik_Archive::LABEL_ECOMMERCE_ORDER,
                                             'testSuffix' => '_Metadata_Goals.Get_Order')),
            array($processedReportApi, array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                             'idGoal'     => Piwik_Archive::LABEL_ECOMMERCE_CART,
                                             'testSuffix' => '_Metadata_Goals.Get_AbandonedCart')),

            // normal standard goal test
            array($processedReportApi, array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                             'idGoal'     => self::$idGoalStandard,
                                             'testSuffix' => '_Metadata_Goals.Get_NormalGoal')),

            // non-existant goal test
            array($processedReportApi, array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'apiModule' => 'Goals', 'apiAction' => 'get',
                                             'idGoal'     => 'FAKE IDGOAL',
                                             'testSuffix' => '_Metadata_Goals.Get_NotExistingGoal')),

            // While we're at it, test for a standard Metadata report with zero entries
            array($processedReportApi, array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'apiModule' => 'VisitTime',
                                             'apiAction'  => 'getVisitInformationPerServerTime',
                                             'testSuffix' => '_Metadata_VisitTime.getVisitInformationPerServerTime')),

            // Standard non metadata Goals.get
            // test Goals.get with idGoal=ecommerceOrder and ecommerceAbandonedCart
            array('Goals.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                     'periods'    => array('day', 'week'), 'idGoal' => Piwik_Archive::LABEL_ECOMMERCE_CART,
                                     'testSuffix' => '_GoalAbandonedCart')),
            array('Goals.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                     'periods'    => array('day', 'week'), 'idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER,
                                     'testSuffix' => '_GoalOrder')),
            array('Goals.get', array('idSite'  => self::$idSite, 'date' => self::$dateTime,
                                     'periods' => array('day', 'week'), 'idGoal' => 1, 'testSuffix' => '_GoalMatchTitle')),
            array('Goals.get', array('idSite'  => self::$idSite, 'date' => self::$dateTime,
                                     'periods' => array('day', 'week'), 'idGoal' => '', 'testSuffix' => '_GoalOverall')),

            array('VisitsSummary.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'segment' => 'visitEcommerceStatus==none',
                                             'testSuffix' => '_SegmentNoEcommerce')),
            array('VisitsSummary.get', array('idSite'  => self::$idSite, 'date' => self::$dateTime,
                                             'periods' => array('day'), 'testSuffix' => '_SegmentOrderedSomething',
                                             'segment' => 'visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart')),
            array('VisitsSummary.get', array('idSite'  => self::$idSite, 'date' => self::$dateTime,
                                             'periods' => array('day'), 'testSuffix' => '_SegmentAbandonedCart',
                                             'segment' => 'visitEcommerceStatus==abandonedCart,visitEcommerceStatus==orderedThenAbandonedCart')),

            // test segment visitConvertedGoalId
            array('VisitsSummary.get', array('idSite'  => self::$idSite, 'date' => self::$dateTime,
                                             'periods' => array('day', 'week'), 'testSuffix' => '_SegmentConvertedGoalId1',
                                             'segment' => "visitConvertedGoalId==".self::$idGoalStandard)),
            array('VisitsSummary.get', array('idSite'  => self::$idSite, 'date' => self::$dateTime,
                                             'periods' => array('day'), 'testSuffix' => '_SegmentDidNotConvertGoalId1',
                                             'segment' => "visitConvertedGoalId!=".self::$idGoalStandard)),

            // test segment visitorType
            array('VisitsSummary.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('week'), 'segment' => 'visitorType==new',
                                             'testSuffix' => '_SegmentNewVisitors')),
            array('VisitsSummary.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('week'), 'segment' => 'visitorType==returning',
                                             'testSuffix' => '_SegmentReturningVisitors')),
            array('VisitsSummary.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('week'), 'segment' => 'visitorType==returningCustomer',
                                             'testSuffix' => '_SegmentReturningCustomers')),

            // test segment pageTitle
            array('VisitsSummary.get', array('idSite'     => self::$idSite, 'date' => self::$dateTime,
                                             'periods'    => array('day'), 'segment' => 'pageTitle==incredible title!',
                                             'testSuffix' => '_SegmentPageTitleMatch')),

            // test Live! output is OK also for the visit that just bought something (other visits leave an abandoned cart)
            array('Live.getLastVisitsDetails', array('idSite'  => self::$idSite,
                                                     'date'    => Piwik_Date::factory(self::$dateTime)->addHour(30.65)->getDatetime(),
                                                     'periods' => array('day'), 'testSuffix' => '_LiveEcommerceStatusOrdered')),

            // test API.get method
            array('API.get', array('idSite'                 => self::$idSite, 'date' => self::$dateTime, 'periods' => array('day', 'week'),
                                   'otherRequestParameters' => array(
                                       'columns' => 'nb_pageviews,nb_visits,avg_time_on_site,nb_visits_converted'),
                                   'testSuffix'             => '_API_get')),

            // Website2
            array($goalWeekApi, array('idSite'     => self::$idSite2, 'date' => self::$dateTime, 'periods' => array('week'),
                                      'testSuffix' => '_Website2')),

			), self::getApiForTestingScheduledReports(self::$dateTime, 'week'));
    }

    public function getOutputPrefix()
    {
        return 'ecommerceOrderWithItems';
    }

    public static function setUpWebsitesAndGoals()
    {
        self::createWebsite(self::$dateTime, $ecommerce = 1);
        self::createWebsite(self::$dateTime);
        Piwik_Goals_API::getInstance()->addGoal(self::$idSite, 'title match, triggered ONCE', 'title', 'incredible', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = true);
    }

    protected static function trackVisits()
    {
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;
        $idSite2  = self::$idSite2;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);
        // VISIT NO 1
        $t->setUrl('http://example.org/index.htm');
        $category = 'Electronics & Cameras';
        $price    = 1111.11111;

        // VIEW product page
        $t->setEcommerceView('SKU2', 'PRODUCT name', $category, $price);
        $t->setCustomVariable(5, 'VisitorType', 'NewLoggedOut', 'visit');
        $t->setCustomVariable(4, 'ValueIsZero', '0', 'visit');
        self::assertTrue($t->getCustomVariable(3, 'page') == array('_pks', 'SKU2'));
        self::assertTrue($t->getCustomVariable(4, 'page') == array('_pkn', 'PRODUCT name'));
        self::assertTrue($t->getCustomVariable(5, 'page') == array('_pkc', $category));
        self::assertTrue($t->getCustomVariable(2, 'page') == array('_pkp', $price));
        self::assertTrue($t->getCustomVariable(5, 'visit') == array('VisitorType', 'NewLoggedOut'));
        self::checkResponse($t->doTrackPageView('incredible title!'));

        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category, $price = 666);
        self::checkResponse($t->doTrackPageView('Another Product page'));

        // Note: here testing to pass a timestamp to the tracking API rather than the datetime string
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getTimestampUTC());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', '');
        self::checkResponse($t->doTrackPageView('Another Product page with no category'));

        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $categories = array('Multiple Category 1', '', 0, 'Multiple Category 2', 'Electronics & Cameras', 'Multiple Category 4', 'Multiple Category 5', 'SHOULD NOT BE REPORTEDSSSSSSSSSSSSSSssssssssssssssssssssssssssstttttttttttttttttttttttuuuu!'));
        self::checkResponse($t->doTrackPageView('Another Product page with multiple categories'));

        // VISIT NO 2

        // Fake the returning visit cookie
        $t->setDebugStringAppend("&_idvc=2");

        // VIEW category page
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.6)->getDatetime());
        $t->setEcommerceView('', '', $category);
        self::checkResponse($t->doTrackPageView('Looking at ' . $category . ' page with a page level custom variable'));

        // VIEW category page again
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.7)->getDatetime());
        $t->setEcommerceView('', '', $category);
        self::checkResponse($t->doTrackPageView('Looking at ' . $category . ' page again'));

        // VIEW product page
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.8)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 666);
        self::checkResponse($t->doTrackPageView('Looking at product page'));

        // ADD TO CART
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.9)->getDatetime());
        $t->setCustomVariable(3, 'VisitorName', 'Great name!', 'visit');
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 500, $quantity = 1);
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $category = 'Electronics & Cameras', $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'SKU WILL BE DELETED', $name = 'BLABLA DELETED', $category = '', $price = 5000000, $quantity = 20);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));

        // ORDER NO 1
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', $categories, $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'ANOTHER SKU HERE', $name = 'PRODUCT name BIS', $category = '', $price = 100, $quantity = 6);
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '937nsjusu 3894', $grandTotal = 1111.11, $subTotal = 1000, $tax = 111, $shipping = 0.11, $discount = 666));

        // ORDER NO 2
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2.1)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR', $category = 'Electronics & Cameras', $price = 1500, $quantity = 1);
        // Product bought with empty category
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name', '', $price = 11.22, $quantity = 1);

        // test to delete all custom vars, they should be copied from visits
        // This is a frequent use case: ecommerce shops tracking the order from backoffice
        // without passing the custom variable 1st party cookie along since it's not known by back office
        $visitorCustomVarSave = $t->visitorCustomVar;
        $t->visitorCustomVar  = false;
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '1037nsjusu4s3894', $grandTotal = 2000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
        $t->visitorCustomVar = $visitorCustomVarSave;

        // ORDER SHOULD DEDUPE
        // Refresh the page with the receipt for the second order, should be ignored
        // we test that both the order, and the products, are not updated on subsequent "Receipt" views
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2.2)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR NOT!', $category = 'Electronics & Cameras NOT!', $price = 15000000000, $quantity = 10000);
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '1037nsjusu4s3894', $grandTotal = 20000000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));

        // Leave with an opened cart
        // No category
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2.3)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart', $category = '', $price = 500.11111112, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));

        // Record the same visit leaving twice an abandoned cart
        foreach (array(0, 5, 24) as $offsetHour) {
            $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.4)->getDatetime());
            // Also recording an order the day after
            if ($offsetHour >= 24) {
                $t->setDebugStringAppend("&_idvc=1");
                $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR', $category = 'Electronics & Cameras', $price = 1500, $quantity = 1);
                self::checkResponse($t->doTrackEcommerceOrder($orderId = '1037nsjusu4s3894', $grandTotal = 20000000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
            }

            // VIEW PRODUCT PAGES
            $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.5)->getDatetime());
            $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart', $category = '', $price = 999);
            self::checkResponse($t->doTrackPageView("View product left in cart"));

            $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.55)->getDatetime());
            $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart', $category = '', $price = 333);
            self::checkResponse($t->doTrackPageView("View product left in cart"));

            $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.6)->getDatetime());
            $t->setEcommerceView($sku = 'SKU IN ABANDONED CART TWO', $name = 'PRODUCT TWO LEFT in cart', $category = 'Category TWO LEFT in cart');
            self::checkResponse($t->doTrackPageView("View product left in cart"));

            // ABANDONED CART
            $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.7)->getDatetime());
            $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart', $category = '', $price = 500.11111112, $quantity = 1);
            $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART TWO', $name = 'PRODUCT TWO LEFT in cart', $category = 'Category TWO LEFT in cart', $price = 1000, $quantity = 2);
            $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart', $category = 'Electronics & Cameras', $price = 10, $quantity = 1);
            self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 2510.11111112));
        }

        // One more Ecommerce order to check weekly archiving works fine on orders
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(30.7)->getDatetime());
        $t->addEcommerceItem($sku = 'TRIPOD SKU', $name = 'TRIPOD - bought day after', $category = 'Tools', $price = 100, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '666', $grandTotal = 240, $subTotal = 200, $tax = 20, $shipping = 20, $discount = 20));

        // One more Ecommerce order, without any product in it, because we still track orders without products
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(30.8)->getDatetime());
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '777', $grandTotal = 10000));

        // testing the same order in a different website should record
        $t = self::getTracker($idSite2, $dateTime, $defaultInit = true);
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(30.9)->getDatetime());
        $t->addEcommerceItem($sku = 'TRIPOD SKU', $name = 'TRIPOD - bought day after', $category = 'Tools', $price = 100, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceOrder($orderId = '777', $grandTotal = 250));
        //------------------------------------- End tracking
    }
}

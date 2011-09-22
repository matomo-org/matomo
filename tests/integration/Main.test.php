<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}


require_once PIWIK_INCLUDE_PATH . '/tests/core/Database.test.php';
require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Runs integration / acceptance tests
 * 
 * The test calls the Piwik tracker with known sets of data, expected errors, 
 * and can _test the output of the tracker beacon, as well as calling 
 * all API functions and compare their XML output with the 'expected output'.
 * 
 * If an algorithm changes in the Tracker or in the Archiving, tests can easily be run to check that 
 * the output changes as expected (eg. More accurate browser detection, adding a new metric in the 
 * API results, etc.
 * 
 * @see Ideas for improvements http://dev.piwik.org/trac/ticket/1465
 */
class Test_Piwik_Integration_Main extends Test_Integration
{
	/*
	 * Path where expected/processed output files are stored
	 */
	public function getPathToTestDirectory()
	{
		return PIWIK_INCLUDE_PATH . '/tests/integration';
	}
	
	function test_ecommerceOrderWithItems()
	{
		$this->setApiNotToCall(array());
		$dateTime = '2011-04-05 00:11:42';
		$idSite = $this->createWebsite($dateTime, $ecommerce=1);
		$idSite2 = $this->createWebsite($dateTime);
		
		$idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'title match, triggered ONCE', 'title', 'incredible', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions = true);
        $idGoalStandard = $idGoal;
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    	// VISIT NO 1
        $t->setUrl( 'http://example.org/index.htm' );
        $category = 'Electronics & Cameras';
        $price = 1111.11111;
        
        // VIEW product page
        $t->setEcommerceView('SKU2', 'PRODUCT name', $category, $price);
        $t->setCustomVariable(5, 'VisitorType', 'NewLoggedOut', 'visit');
        $this->assertTrue($t->getCustomVariable(3, 'page') == array('_pks','SKU2'));
        $this->assertTrue($t->getCustomVariable(4, 'page') == array('_pkn','PRODUCT name'));
        $this->assertTrue($t->getCustomVariable(5, 'page') == array('_pkc',$category));
        $this->assertTrue($t->getCustomVariable(2, 'page') == array('_pkp',$price));
        $this->assertTrue($t->getCustomVariable(5, 'visit') == array('VisitorType','NewLoggedOut' ));
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , $category, $price=666);
        $this->checkResponse($t->doTrackPageView( 'Another Product page'));

        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , '');
        $this->checkResponse($t->doTrackPageView( 'Another Product page with no category'));

        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , $categories = array('Multiple Category 1', '', 0, 'Multiple Category 2', 'Electronics & Cameras', 'Multiple Category 4', 'Multiple Category 5', 'SHOULD NOT BE REPORTEDSSSSSSSSSSSSSSssssssssssssssssssssssssssstttttttttttttttttttttttuuuu!'));
        $this->checkResponse($t->doTrackPageView( 'Another Product page with multiple categories'));
        
        // VISIT NO 2
        
        // VIEW category page
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.6)->getDatetime());
        $t->setEcommerceView('','', $category);
        $this->checkResponse($t->doTrackPageView( 'Looking at '.$category.' page with a page level custom variable'));

        // VIEW category page again
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.7)->getDatetime());
        $t->setEcommerceView('','', $category);
        $this->checkResponse($t->doTrackPageView( 'Looking at '.$category.' page again'));
        
        // VIEW product page
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.8)->getDatetime());
        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , $category = 'Electronics & Cameras', $price = 666);
        $this->checkResponse($t->doTrackPageView( 'Looking at product page'));
        
        // ADD TO CART
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.9)->getDatetime());
        $t->setCustomVariable(3, 'VisitorName', 'Great name!', 'visit');
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , $category = 'Electronics & Cameras', $price = 500, $quantity = 1);
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , $category = 'Electronics & Cameras', $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'SKU WILL BE DELETED', $name = 'BLABLA DELETED' , $category = '', $price = 5000000, $quantity = 20);
        $this->checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));

        // ORDER NO 1
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , $categories, $price = 500, $quantity = 2);
        $t->addEcommerceItem($sku = 'ANOTHER SKU HERE', $name = 'PRODUCT name BIS' , $category = '', $price = 100, $quantity = 6);
        $this->checkResponse($t->doTrackEcommerceOrder($orderId = '937nsjusu 3894', $grandTotal = 1111.11, $subTotal = 1000, $tax = 111, $shipping = 0.11, $discount = 666));
        
        // ORDER NO 2
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2.1)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR' , $category = 'Electronics & Cameras', $price = 1500, $quantity = 1);
        // Product bought with empty category
		$t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT name' , '', $price = 11.22, $quantity = 1);
        $this->checkResponse($t->doTrackEcommerceOrder($orderId = '1037nsjusu4s3894', $grandTotal = 2000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
       
        // ORDER SHOULD DEDUPE
        // Refresh the page with the receipt for the second order, should be ignored
        // we test that both the order, and the products, are not updated on subsequent "Receipt" views
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2.2)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR NOT!' , $category = 'Electronics & Cameras NOT!', $price = 15000000000, $quantity = 10000); 
        $this->checkResponse($t->doTrackEcommerceOrder($orderId = '1037nsjusu4s3894', $grandTotal = 20000000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
        
        // Leave with an opened cart
        // No category
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2.3)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart' , $category = '', $price = 500.11111112, $quantity = 2);
        $this->checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));

        // Record the same visit leaving twice an abandoned cart
        foreach(array(0, 5, 24) as $offsetHour)
        {   
	        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.4)->getDatetime());
        	// Also recording an order the day after
        	if($offsetHour >= 24)
        	{
	        	$t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR' , $category = 'Electronics & Cameras', $price = 1500, $quantity = 1);
	        	$this->checkResponse($t->doTrackEcommerceOrder($orderId = '1037nsjusu4s3894', $grandTotal = 20000000, $subTotal = 1500, $tax = 400, $shipping = 100, $discount = 0));
        	}
        	
        	// VIEW PRODUCT PAGES
	        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.5)->getDatetime());
	        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart' , $category = '', $price=999);
	        $this->checkResponse($t->doTrackPageView("View product left in cart"));
	        
	        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.55)->getDatetime());
	        $t->setEcommerceView($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart' , $category = '', $price=333);
	        $this->checkResponse($t->doTrackPageView("View product left in cart"));
	        
	        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.6)->getDatetime());
	        $t->setEcommerceView($sku = 'SKU IN ABANDONED CART TWO', $name = 'PRODUCT TWO LEFT in cart' , $category = 'Category TWO LEFT in cart');
	        $this->checkResponse($t->doTrackPageView("View product left in cart"));

	        // ABANDONED CART
	        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour($offsetHour + 2.7)->getDatetime());
	        $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart' , $category = '', $price = 500.11111112, $quantity = 1);
	        $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART TWO', $name = 'PRODUCT TWO LEFT in cart' , $category = 'Category TWO LEFT in cart', $price = 1000, $quantity = 2);
	        $t->addEcommerceItem($sku = 'SKU VERY nice indeed', $name = 'PRODUCT THREE LEFT in cart' , $category = 'Electronics & Cameras', $price = 10, $quantity = 1);
	        $this->checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 2510.11111112));
        }
        
        // One more Ecommerce order to check weekly archiving works fine on orders
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour( 30.7 )->getDatetime());
        $t->addEcommerceItem($sku = 'TRIPOD SKU', $name = 'TRIPOD - bought day after' , $category = 'Tools', $price = 100, $quantity = 2);
        $this->checkResponse($t->doTrackEcommerceOrder($orderId = '666', $grandTotal = 240, $subTotal = 200, $tax = 20, $shipping = 20, $discount = 20));
        
        // One more Ecommerce order, without any product in it, because we still track orders without products
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour( 30.8 )->getDatetime());
        $this->checkResponse($t->doTrackEcommerceOrder($orderId = '777', $grandTotal = 10000));
        
        // Testing the same order in a different website should record
        $t = $this->getTracker($idSite2, $dateTime, $defaultInit = true);
    	$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour( 30.9 )->getDatetime());
        $t->addEcommerceItem($sku = 'TRIPOD SKU', $name = 'TRIPOD - bought day after' , $category = 'Tools', $price = 100, $quantity = 2);
        $this->checkResponse($t->doTrackEcommerceOrder($orderId = '777', $grandTotal = 250));
        //------------------------------------- End tracking
        
		// From Piwik 1.5, we hide Goals.getConversions and other get* methods via @ignore, but we ensure that they still work
		// This hack allows the API proxy to let us generate example URLs for the ignored functions
		Piwik_API_Proxy::getInstance()->hideIgnoredFunctions = false;
        
		$this->setApiToCall( array('VisitsSummary.get', 'VisitTime', 'CustomVariables.getCustomVariables', 'Live.getLastVisitsDetails', 'UserCountry', 'API.getProcessedReport', 'Goals.get', 'Goals.getConversions', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'	) );
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime, $periods = array('day'));
		$this->setApiToCall( array('Goals.get', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'	) );
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime, $periods = array('week'));

		// Abandoned carts calls
        $abandonedCarts = 1;
		$this->setApiToCall( array('Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory') );
        $this->callGetApiCompareOutput(__FUNCTION__ . '_AbandonedCarts', 'xml', $idSite, $dateTime, $periods = array('day', 'week'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts);
        // test with multiple periods
        $this->callGetApiCompareOutput(__FUNCTION__ . 'multipleDates', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN=true);
        
        // test with multiple periods and multiple websites
        $this->callGetApiCompareOutput(__FUNCTION__ . 'multipleDates_andMultipleWebsites', 'xml', $idSites = "$idSite,$idSite2", $dateTime, $periods = array('day'), $setDateLastN=true);
        
        // Test metadata products
		$this->setApiToCall( array('API.getProcessedReport'	) );
        $apiModule = 'Goals';
        $apiAction = 'getItemsSku';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_ItemsSku', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal = false, $apiModule, $apiAction);
        $apiModule = 'Goals';
        $apiAction = 'getItemsCategory';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_ItemsCategory', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal = false, $apiModule, $apiAction);

        // Test metadata Goals.get for Ecommerce orders & Carts
        $idGoal = Piwik_Archive::LABEL_ECOMMERCE_ORDER;
        $apiModule = 'Goals';
        $apiAction = 'get';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_Goals.Get_Order', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal, $apiModule, $apiAction);
        $idGoal = Piwik_Archive::LABEL_ECOMMERCE_CART;
        $apiModule = 'Goals';
        $apiAction = 'get';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_Goals.Get_AbandonedCart', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal, $apiModule, $apiAction);
        // Normal standard goal
        $idGoal = $idGoalStandard;
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_Goals.Get_NormalGoal', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal, $apiModule, $apiAction);
        // Non existing goal id should return error
        $idGoal = 'FAKE IDGOAL';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_Goals.Get_NotExistingGoal', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal, $apiModule, $apiAction);
        
        // While we're at it, test for a standard Metadata report with zero entries 
        $apiModule = 'VisitTime';
        $apiAction = 'getVisitInformationPerServerTime';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Metadata_VisitTime.getVisitInformationPerServerTime', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal = false, $apiModule, $apiAction);
        
        // Standard non metadata Goals.get
        // Test Goals.get with idGoal=ecommerceOrder and ecommerceAbandonedCart
        $this->setApiToCall( array('Goals.get') );
        $idGoal = Piwik_Archive::LABEL_ECOMMERCE_CART;
        $this->callGetApiCompareOutput(__FUNCTION__ . '_GoalAbandonedCart', 'xml', $idSite, $dateTime, $periods = array('day', 'week'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal);
        $idGoal = Piwik_Archive::LABEL_ECOMMERCE_ORDER;
        $this->callGetApiCompareOutput(__FUNCTION__ . '_GoalOrder', 'xml', $idSite, $dateTime, $periods = array('day', 'week'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal);
        $idGoal = 1;
        $this->callGetApiCompareOutput(__FUNCTION__ . '_GoalMatchTitle', 'xml', $idSite, $dateTime, $periods = array('day', 'week'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal);
        $idGoal = '';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_GoalOverall', 'xml', $idSite, $dateTime, $periods = array('day', 'week'), $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal);
        
        $this->setApiToCall( array('VisitsSummary.get') );
        $segment = 'visitEcommerceStatus==none';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentNoEcommerce', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment);
        $segment = 'visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentOrderedSomething', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment);
        $segment = 'visitEcommerceStatus==abandonedCart,visitEcommerceStatus==orderedThenAbandonedCart';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentAbandonedCart', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment);
        
        // Test segment visitConvertedGoalId
        $segment = 'visitConvertedGoalId=='.$idGoalStandard;
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentConvertedGoalId1', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment);
        $segment = 'visitConvertedGoalId!='.$idGoalStandard;
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentDidNotConvertGoalId1', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment);
        
        // Test segment visitorType
        $segment = 'visitorType==new';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentNewVisitors', 'xml', $idSite, $dateTime, $periods = array('week'), $setDateLastN = false, $language = false, $segment);
        $segment = 'visitorType==returning';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentReturningVisitors', 'xml', $idSite, $dateTime, $periods = array('week'), $setDateLastN = false, $language = false, $segment);
        $segment = 'visitorType==returningCustomer';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentReturningCustomers', 'xml', $idSite, $dateTime, $periods = array('week'), $setDateLastN = false, $language = false, $segment);
        
        // Test segment pageTitle 
        $segment = 'pageTitle==incredible%20title!';
        $this->callGetApiCompareOutput(__FUNCTION__ . '_SegmentPageTitleMatch', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = false, $language = false, $segment);
        
        // test Live! output is OK also for the visit that just bought something (other visits leave an abandoned cart)
        $this->setApiToCall(array('Live.getLastVisitsDetails'));
        $this->callGetApiCompareOutput(__FUNCTION__ . '_LiveEcommerceStatusOrdered', 'xml', $idSite, Piwik_Date::factory($dateTime)->addHour( 30.65 )->getDatetime(), $periods = array('day'));
        
        // Website2
		$this->setApiToCall( array('Goals.get', 'Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'	) );
        $this->callGetApiCompareOutput(__FUNCTION__ . '_Website2', 'xml', $idSite2, $dateTime, $periods = array('week'));
	 
	}
	
	function test_trackGoals_allowMultipleConversionsPerVisit()
	{
		$this->setApiToCall(array(
			'VisitTime.getVisitInformationPerServerTime', 
			'VisitsSummary.get', 
		));
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		
		// First, a goal that is only recorded once per visit
		$allowMultipleConversions = false;
        $idGoal_OneConversionPerVisit = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js ONCE', 'title', 'Thank you', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions);
        // Second, a goal allowing multiple conversions
        $allowMultipleConversions = true;
		$defaultRevenue = 10;
        $idGoal_MultipleConversionPerVisit = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js MULTIPLE ALLOWED', 'manually', '', '', $caseSensitive=false, $defaultRevenue, $allowMultipleConversions);
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st goal, should only have 1 conversion
        $t->setUrl( 'http://example.org/index.htm' );
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackPageView('Thank you mate'));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 10000000));

        // Record 2nd goal, should record both conversions
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.5)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 300));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.6)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 366));
        
        // Update & set to not allow multiple
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $goal = $goals[$idGoal_OneConversionPerVisit];
        $this->assertTrue($goal['allow_multiple'] == 0);
        Piwik_Goals_API::getInstance()->updateGoal($idSite, $idGoal_OneConversionPerVisit, $goal['name'], @$goal['match_attribute'], @$goal['pattern'], @$goal['pattern_type'], @$goal['case_sensitive'], $goal['revenue'], $goal['allow_multiple'] = 1);
        $this->assertTrue($goal['allow_multiple'] == 1);
        
        // 1st goal should Now be tracked
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.61)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 656));

        // Compare XML
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
        
        // Test delete is working as expected
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $this->assertTrue( 2 == count($goals) );
        Piwik_Goals_API::getInstance()->deleteGoal($idSite, $idGoal_OneConversionPerVisit);
        Piwik_Goals_API::getInstance()->deleteGoal($idSite, $idGoal_MultipleConversionPerVisit);
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $this->assertTrue( empty($goals) );
	}
	
	/**
	 * This tests the output of the API plugin API 
	 * It will return metadata about all API reports from all plugins
	 * as well as the data itself, pre-processed and ready to be displayed
	 * @return 
	 */
	function test_apiGetReportMetadata()
	{
		$this->setApiNotToCall(array());
		$this->setApiToCall( 'API' );
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime, $ecommerce = 1);
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions = 1);
        $idGoal2 = Piwik_Goals_API::getInstance()->addGoal($idSite, 'Goal 2 - Hello', 'url', 'hellow', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions = 0);
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    	// Record 1st page view
        $t->setUrl( 'http://example.org/index.htm' );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42.256));
        
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	
	/**
	 * Test the Yearly metadata API response, 
	 * with no visits, with custom response language 
	 */
	function test_apiGetReportMetadata_year()
	{
		$this->setApiNotToCall(array());
		$this->setApiToCall( array('API.getProcessedReport', 
									'API.getReportMetadata', 
									'LanguagesManager.getTranslationsForLanguage', 
									'LanguagesManager.getAvailableLanguageNames',
									'SitesManager.getJavascriptTag') );
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		$language = 'fr';
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime, 'year', $setDateLastN = false, $language);
	}
	
	/*
	 * Testing various wrong Tracker requests and check that they behave as expected:
	 * not throwing errors and not recording data.
	 *  
	 * API will archive and output empty stats.
	 * 
	 */
	function test_noVisit()
	{
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		
		// Trigger invalid website
		$trackerInvalidWebsite = $this->getTracker($idSiteFake = 0, $dateTime, $defaultInit = true);
		$response = Piwik_Http::fetchRemoteFile($trackerInvalidWebsite->getUrlTrackPageView());
		$this->assertTrue(strpos($response, 'Invalid idSite') !== false, 'invalid website ID');

		// Trigger wrong website
		$trackerWrongWebsite = $this->getTracker($idSiteFake = 33, $dateTime, $defaultInit = true);
		$response = Piwik_Http::fetchRemoteFile($trackerWrongWebsite->getUrlTrackPageView());
		$this->assertTrue(strpos($response, 'The requested website id = 33 couldn\'t be found') !== false, 'non-existent website ID');

		// Trigger empty request
		$trackerUrl = $this->getTrackerUrl();
		$response = Piwik_Http::fetchRemoteFile($trackerUrl);
		$this->assertTrue(strpos($response, 'web analytics') !== false, 'Piwik empty request response not correct: ' . $response);
		
		$t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
		
		// test GoogleBot UA visitor
		$t->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
		$this->checkResponse($t->doTrackPageView('bot visit, please do not record'));
		
		// test with excluded IP
		$t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)'); // restore normal user agent	
		$excludedIp = '154.1.12.34';
		Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new site name', $ecommerce = 0, null, $excludedIp . ',1.2.3.4');
		$t->setIp($excludedIp);
		$this->checkResponse($t->doTrackPageView('visit from IP excluded'));
		
		// test with global list of excluded IPs 
		$excludedIpBis = '145.5.3.4';
		Piwik_SitesManager_API::getInstance()->setGlobalExcludedIps($excludedIpBis);
		$t->setIp($excludedIpBis);
		$this->checkResponse($t->doTrackPageView('visit from IP globally excluded'));
		
		try {
			@$t->setAttributionInfo(array());
			$this->fail();
		} catch(Exception $e) {}
		
		try {
			$t->setAttributionInfo(json_encode('test'));
			$this->fail();
		} catch(Exception $e) {}
		
		$t->setAttributionInfo(json_encode(array()));
		
		// this will output empty XML result sets as no visit was tracked
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
        $this->callGetApiCompareOutput(__FUNCTION__ . '_PeriodIsLast', 'xml', $idSite, $dateTime, $periods = array('day', 'week'), $setLastN = true);
	}
	
	
	/*
	 * This use case covers many simple tracking features.
	 * - Tracking Goal by manual trigger, and URL matching, with custom revenue
	 * - Tracking the same Goal twice only records it once
	 * - Tracks 2 page views, a click and a file download
	 * - URLs parameters exclude is tested
	 * - In a returning visit, tracks a Goal conversion 
	 *   URL matching, with custom referer and keyword
	 *   
	 *   NO cookie support
	 */
	function test_OneVisitorTwoVisits() 
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-03-06 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        
		$t->disableCookieSupport();
        $this->doTest_oneVisitorTwoVisits($t, $dateTime, $idSite );
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	
	/*
	 * Same as before, but with cookie support, which incurs some slight changes 
	 * in the reporting data (more accurate unique visitor count, better referer tracking for goals, etc.)
	 */
	function test_OneVisitorTwoVisits_withCookieSupport() 
	{
		$this->setApiNotToCall(array());
    	$this->setApiToCall(array('VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'UserSettings', 'UserCountry', 'Referers', 'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome', 'Actions', 'Live.getLastVisitsDetails'));
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-03-06 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        $t->DEBUG_APPEND_URL = '&forceUseThirdPartyCookie=1';
        $this->doTest_oneVisitorTwoVisits($t, $dateTime, $idSite );
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	

	private function doTest_oneVisitorTwoVisits($t, $dateTime, $idSite )
	{
        $t->setUrlReferrer( 'http://referer.com/page.htm?param=valuewith some spaces');
    	
    	// Testing URL excluded parameters
    	$parameterToExclude = 'excluded_parameter';
    	Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new name', $ecommerce = 0, null, null, $parameterToExclude . ',anotherParameter');

    	// Record 1st page view
    	$urlPage1 = 'http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY&parameter=Should display';
        $t->setUrl( $urlPage1 );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        // Testing that / and index.htm above record with different URLs
        // Recording the 2nd page after 3 minutes
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $urlPage2 = 'http://example.org/' ;
        $t->setUrl( $urlPage2 );
//        $t->setUrlReferrer($urlPage1);
        $this->checkResponse($t->doTrackPageView( 'Second page view - should be registered as URL /'));
        
//        $t->setUrlReferrer($urlPage2);
        // Click on external link after 6 minutes (3rd action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $this->checkResponse($t->doTrackAction( 'http://dev.piwik.org/svn', 'link' ));
        
        // Click on file download after 12 minutes (4th action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $this->checkResponse($t->doTrackAction( 'http://piwik.org/path/again/latest.zip', 'download' ));
        
        // Create Goal 1: Triggered by JS, after 18 minutes
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));
        
        // Track same Goal twice (after 24 minutes), should only be tracked once
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));
        
        // Final page view (after 27 min)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.45)->getDatetime());
        $t->setUrl( 'http://example.org/index.htm' );
        $this->checkResponse($t->doTrackPageView( 'Looking at homepage (again)...'));
        
        // -
        // End of first visit: 24min
        
        // Create Goal 2: Matching on URL
        Piwik_Goals_API::getInstance()->addGoal($idSite, 'matching purchase.htm', 'url', '(.*)store\/purchase\.(.*)', 'regex', false, $revenue = 1);

        // -
        // Start of returning visit, 1 hour after first page view
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrl( 'http://example.org/store/purchase.htm' );
        $t->setUrlReferrer( 'http://search.yahoo.com/search?p=purchase');
        
        // Goal Tracking URL matching, testing custom referer including keyword
        $this->checkResponse($t->doTrackPageView( 'Checkout/Purchasing...'));
        // -
        // End of second visit
	}
	
	/*
	 * Tests Tracker several websites, different days.
	 * Tests API for period=day/week/month/year, requesting data for both websites, 
	 * and requesting data for last N periods.
	 * Also tests a visit that spans over 2 days.
	 * And testing empty URL and empty Page name request
	 * Also testing a click on a mailto counted as outlink
	 * Also testing metadata API for multiple periods
	 */
	function test_TwoVisitors_twoWebsites_differentDays()
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-01-03 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
    	$idSite2 = $this->createWebsite($dateTime);
		$apiToCall = array('VisitFrequency.get',
							'VisitsSummary.get',
							'Referers.getWebsites',
							'Actions.getPageUrls',
							'Actions.getPageTitles',
							'Actions.getOutlinks');
    	$this->setApiToCall($apiToCall);
    	// -
    	// First visitor on Idsite 1: two page views
    	$datetimeSpanOverTwoDays = '2010-01-03 23:55:00'; 
        $visitorA = $this->getTracker($idSite, $datetimeSpanOverTwoDays, $defaultInit = true);
        $visitorA->setUrlReferrer( 'http://referer.com/page.htm?param=valuewith some spaces');
        $visitorA->setUrl('http://example.org/homepage');
        $this->checkResponse($visitorA->doTrackPageView('first page view'));
    	$visitorA->setForceVisitDateTime(Piwik_Date::factory($datetimeSpanOverTwoDays)->addHour(0.1)->getDatetime());
    	// Testing with empty URL and empty page title
    	$visitorA->setUrl('  ');
        $this->checkResponse($visitorA->doTrackPageView('  '));
        
        // - 
    	// Second new visitor on Idsite 1: one page view 
        $visitorB = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    	$visitorB->setIp('100.52.156.83');
    	$visitorB->setResolution(800, 300);
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $visitorB->setUrlReferrer( '' );
    	$visitorB->setUserAgent('Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1');
    	$visitorB->setUrl('http://example.org/products');
    	$this->checkResponse($visitorB->doTrackPageView('first page view'));

    	// -
    	// Second visitor again on Idsite 1: 2 page views 2 days later, 2010-01-05
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->getDatetime());
		// visitor_returning is set to 1 only when visit count more than 1
		// Temporary, until we implement 1st party cookies in PiwikTracker
        $visitorB->DEBUG_APPEND_URL = '&_idvc=2';

    	$visitorB->setUrlReferrer( 'http://referer.com/Other_Page.htm' );
    	$visitorB->setUrl('http://example.org/homepage');
    	$this->checkResponse($visitorB->doTrackPageView('second visitor/two days later/a new visit'));
    	// Second page view 6 minutes later
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.1)->getDatetime());
    	$visitorB->setUrl('http://example.org/thankyou');
    	$this->checkResponse($visitorB->doTrackPageView('second visitor/two days later/second page view'));
    	
    	// Testing a strange combination causing an error in r3767
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.2)->getDatetime());
    	$this->checkResponse($visitorB->doTrackAction('mailto:test@example.org', 'link'));
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.25)->getDatetime());
    	$this->checkResponse($visitorB->doTrackAction('mailto:test@example.org/strangelink', 'link'));
    	
    	// -
    	// First visitor on Idsite 2: one page view, with Website referer
        $visitorAsite2 = $this->getTracker($idSite2, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime(), $defaultInit = true);
        $visitorAsite2->setUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0;)');
        $visitorAsite2->setUrlReferrer('http://only-homepage-referer.com/');
        $visitorAsite2->setUrl('http://example2.com/home');
        $this->checkResponse($visitorAsite2->doTrackPageView('Website 2 page view'));
        
        // Returning visitor on Idsite 2 1 day later, one page view, with chinese referer
//    	$t2->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48 + 10)->getDatetime());
//        $t2->setUrlReferrer('http://www.baidu.com/s?wd=%D0%C2+%CE%C5&n=2');
//        $t2->setUrl('http://example2.com/home');
//        $this->checkResponse($t2->doTrackPageView('I\'m a returning visitor...'));
        
    	$periods = array('day', 'week', 'month', 'year');
    	// Request data for the last 6 periods
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $allSites = 'all', $dateTime, $periods, $setDateLastN = true);
        
        // We also test a single period to check that this use case (Reports per idSite in the response) works
    	$this->setApiToCall(array('VisitsSummary.get', 'Goals.get'));
    	$this->callGetApiCompareOutput(__FUNCTION__ . '_NotLastNPeriods', 'xml', $allSites = 'all', $dateTime, array('day', 'month'), $setDateLastN = false);
		
		// testing metadata API for multiple periods
		$this->setApiNotToCall(array());
		$this->setApiToCall( array('API.getProcessedReport'	) );
		foreach($apiToCall as $api)
		{
			list($apiModule, $apiAction) = explode(".", $api);
			$this->callGetApiCompareOutput(__FUNCTION__ . '_'.$api.'_firstSite_lastN', 'xml', $idSite, $dateTime, $periods = array('day'), $setDateLastN = true, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false, $idGoal = false, $apiModule, $apiAction);
		}
	}
	
	private function doTest_twoVisitsWithCustomVariables($dateTime, $width=1111, $height=222)
	{        
	    // Tests run in UTC, the Tracker in UTC
    	$idSite = $this->createWebsite($dateTime);
    	$this->setApiToCall(array(	'VisitsSummary.get',
    	                            'CustomVariables.getCustomVariables'
    	));
    	ob_start();
		$idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
				$idGoal2 = Piwik_Goals_API::getInstance()->addGoal($idSite, 'second goal', 'manually', '', '');
        $visitorA = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        // Used to test actual referer + keyword position in Live!
        $visitorA->setUrlReferrer(urldecode('http://www.google.com/url?sa=t&source=web&cd=1&ved=0CB4QFjAA&url=http%3A%2F%2Fpiwik.org%2F&rct=j&q=this%20keyword%20should%20be%20ranked&ei=V8WfTePkKKLfiALrpZWGAw&usg=AFQjCNF_MGJRqKPvaKuUokHtZ3VvNG9ALw&sig2=BvKAdCtNixsmfNWXjsNyMw'));
        
        // no campaign, but a search engine to attribute the goal conversion to
        $attribution = array(
        	'',
        	'',
        	1302306504,
        	'http://www.google.com/search?q=piwik&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:en-GB:official&client=firefox-a'
        );
        $visitorA->setAttributionInfo(json_encode($attribution));
        
        $visitorA->setResolution($width, $height);
        
        // At first, visitor custom var is set to LoggedOut
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
    	$visitorA->setUrl('http://example.org/homepage');
    	$visitorA->setCustomVariable($id = 1, $name = 'VisitorType', $value = 'LoggedOut');
        $this->checkResponse($visitorA->doTrackPageView('Homepage'));
        $this->checkResponse($visitorA->doTrackGoal($idGoal2));
                
        // After login, set to LoggedIn, should overwrite previous value
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
    	$visitorA->setUrl('http://example.org/user/profile');
    	$visitorA->setCustomVariable($id = 1, $name = 'VisitorType', $value = 'LoggedIn');
        $this->checkResponse($visitorA->doTrackPageView('Profile page'));
        
    	$visitorA->setCustomVariable($id = 2, $name = 'SET WITH EMPTY VALUE', $value = '');
    	$visitorA->setCustomVariable($id = 3, $name = 'Value will be VERY long and truncated', $value = 'abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----');
        $this->checkResponse($visitorA->doTrackPageView('Profile page'));
    	$this->checkResponse($visitorA->doTrackGoal($idGoal));
    	
        // - 
    	// Second new visitor on Idsite 1: one page view 
        $visitorB = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        $visitorB->setUrlReferrer('');
        
        $attribution = array(
        	'CAMPAIGN NAME - YEAH!',
        	'CAMPAIGN KEYWORD - RIGHT...',
        	1302306504,
        	'http://www.example.org/test/really?q=yes'
        );
        $visitorB->setAttributionInfo(json_encode($attribution));
        $visitorB->setResolution($width, $height);
    	$visitorB->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6');
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
    	$visitorB->setCustomVariable($id = 1, $name = 'VisitorType', $value = 'LoggedOut');
    	$visitorB->setCustomVariable($id = 2, $name = 'Othercustom value which should be truncated abcdefghijklmnopqrstuvwxyz', $value = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz');
    	$visitorB->setCustomVariable($id = -2, $name = 'not tracked', $value = 'not tracked');
    	$visitorB->setCustomVariable($id = 6, $name = 'not tracked', $value = 'not tracked');
    	$visitorB->setCustomVariable($id = 6, $name = array('not tracked'), $value = 'not tracked');
    	$visitorB->setUrl('http://example.org/homepage');
    	$this->checkResponse($visitorB->doTrackGoal($idGoal, 1000));
    	
    	// DIFFERENT TEST -
    	// Testing that starting the visit with an outlink works (doesn't trigger errors)
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
    	$this->checkResponse($visitorB->doTrackAction('http://test.com', 'link'));

    	// hack
    	$this->visitorId = $visitorB->getVisitorId();
    	return $idSite;
	}
	
	function test_twoVisitsWithCustomVariables()
	{
		$dateTime = '2010-01-03 11:22:33';
        $this->doTest_twoVisitsWithCustomVariables($dateTime);
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', 
        								$idSite = 'all', 
        								$dateTime, 
        								$periods = array('day', 'week'), 
        								$setDateLastN = true);
	}

	function test_twoVisitsWithCustomVariables_segmentMatchVisitorType()
	{
		$dateTime = '2010-01-03 11:22:33';
        $this->doTest_twoVisitsWithCustomVariables($dateTime);
        
        // Segment matching some
        $segments = array(
        	'customVariableName1==VisitorType;customVariableValue1==LoggedIn',
        	'customVariableName1==VisitorType;customVariableValue1=@LoggedI',
		);
		
		// We run it twice just to check that running archiving twice for same input parameters doesn't create more records/overhead
		for($i = 1; $i <= 2; $i++)
		{
			foreach($segments as $segment)
			{
		        $this->setApiToCall(array(	
		        							'Referers.getKeywords',
		    	                            'CustomVariables.getCustomVariables',
		        							'VisitsSummary.get',
		    	));
		        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', 
		        								$idSite = 'all', 
		        								$dateTime, 
		        								$periods = array('day', 'week'), 
		        								$setDateLastN = true,
		        								$language=false, 
		        								$segment
		        );
			}
		}
		
		// ----------------------------------------------
        // Implementation Checks
        // ---------------------------------------------- 
        // Verify that, when a segment is specified, only the requested report is processed
        // In this case, check that only the Custom Variables blobs have been processed
        
		$tests = array(
	        // 1) CHECK 'day' archive stored in January
	        // We expect 2 segments * (2 custom variable name + 2 ref metrics + 2 subtable for the custom var values + 5 Referers blob)
			'archive_blob_2010_01' => 22,
			// This contains all 'last N' weeks & days, (6 metrics + 2 referer metrics + 1 done flag ) * 2 segments + 1 Done flag per Plugin, for each "Last N" date
			'archive_numeric_2010_01' => 138,
		
	        // 2) CHECK 'week' archive stored in December (week starts the month before)
	        // We expect 2 segments * (2 custom variable name + 2 ref metrics + 2 subtable for the values of the name + 5 referers blob)
			'archive_blob_2009_12' => 22,
	        // 6 metrics, 
	        // 2 Referer metrics (Referers_distinctSearchEngines/Referers_distinctKeywords), 
	        // 3 done flag (referers, CustomVar, VisitsSummary), 
	        // X * 2 segments
			'archive_numeric_2009_12' => (6 + 2 + 3) * 2,
		);
		foreach($tests as $table => $expectedRows)
		{
	        $sql = "SELECT count(*) FROM " . Piwik_Common::prefixTable($table) ;
	        $countBlobs = Zend_Registry::get('db')->fetchOne($sql);
	        $this->assertEqual( $expectedRows, $countBlobs);
		}
	}
	
	function test_twoVisitsWithCustomVariables_segmentMatchALL_noGoalData()
	{
		$dateTime = '2010-01-03 11:22:33';
        $width=1111; $height=222; $resolution = $width.'x'.$height;
        $this->doTest_twoVisitsWithCustomVariables($dateTime, $width, $height);
        
        // Segment matching ALL
        // + adding DOES NOT CONTAIN segment always matched, to test this particular operator
        $segment = 'resolution=='.$resolution.';customVariableName1!@randomvalue%20does%20not%20exist';
    	
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', 
        								$idSite = 'all', 
        								$dateTime, 
        								$periods = array('day', 'week'), 
        								$setDateLastN = true,
        								$language=false, 
        								$segment
        );
	}
	
	/* Testing a segment containing all supported fields */
	function test_twoVisitsWithCustomVariables_segmentMatchNONE()
	{
		$dateTime = '2010-01-03 11:22:33';
        $idSite = $this->doTest_twoVisitsWithCustomVariables($dateTime);
        
        // Segment matching NONE
        $segments = Piwik_API_API::getInstance()->getSegmentsMetadata($idSite);
        $segmentExpression = array();
        
        $seenVisitorId = false;
		foreach($segments as $segment) { 
			$value = 'campaign';
			if($segment['segment'] == 'visitorId')
			{
				$seenVisitorId = true;
				$value = '34c31e04394bdc63';
			}
			if($segment['segment'] == 'visitEcommerceStatus')
			{
				$value = 'none';
			}
			$segmentExpression[] = $segment['segment'] .'!='.$value;
		}
		// just checking that this segment was tested (as it has the only visible to admin flag)
		$this->assertTrue($seenVisitorId);
		
        $segment = implode(";", $segmentExpression);
        $this->assertTrue(strlen($segment) > 100);
//        echo $segment;
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', 
        								$idSite = 'all', 
        								$dateTime, 
        								$periods = array('day', 'week'), 
        								$setDateLastN = true,
        								$language=false, 
        								$segment
        );
	}
	
	/*
	 * Testing period=range use case. Recording data before and after, checking that the requested range is processed correctly 
	 */
	public function test_oneVisitor_oneWebsite_severalDays_DateRange()
	{        
    	$dateTimes = array(
    		'2010-12-14 01:00:00',
    		'2010-12-15 01:00:00',
    		'2010-12-25 01:00:00',
    		'2011-01-15 01:00:00',
    		'2011-01-16 01:00:00',
    	);
    	$idSite = $this->createWebsite($dateTimes[0]);
    	
    	foreach($dateTimes as $dateTime)
    	{
	        $visitor = $this->getTracker($idSite, $dateTime, $defaultInit = true);
	        
	        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
	    	$visitor->setUrl('http://example.org/homepage');
	        $this->checkResponse($visitor->doTrackPageView('ou pas'));
	
	        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
	    	$visitor->setUrl('http://example.org/news');
	        $this->checkResponse($visitor->doTrackPageView('ou pas'));
	
	        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
	    	$visitor->setUrl('http://example.org/news');
	        $this->checkResponse($visitor->doTrackPageView('ou pas'));
    	}
    	
    	// 2 segments: ALL and another way of expressing ALL but triggering the Segment code path 
    	$segments = array(
    		false,
    		'country!=aa',
    	);
    	// Running twice just as health check that second call also works
    	for($i = 0; $i <=1; $i++)
    	{
    		foreach($segments as $segment)
    		{
				$this->setApiToCall(array(
		    	                            'Actions.getPageUrls',
		    	                            'VisitsSummary.get',
		    	                            'UserSettings.getResolution',
		    	                            'VisitFrequency.get',
		    	));
				$this->callGetApiCompareOutput(__FUNCTION__, 'xml', 
		        								$idSite, 
		        								$date = '2010-12-15,2011-01-15', 
		        								$periods = array('range'),
		        								$setDateLastN = false,
		        								$language = false,
		        								$segment
		        );
    		}
    	}
	        
        // Check that requesting period "Range" means 
        // only processing the requested Plugin blob (Actions in this case), not all Plugins blobs
		$tests = array(
			// 4 blobs for the Actions plugin, 7 blogs for UserSettings
			'archive_blob_2010_12' => (4 + 7) * 2, 
			// (VisitsSummary 5 metrics + 1 flag - no Unique visitors for range) + 2 Flags archive Actions/UserSettings + (Frequency 5 metrics + 1 flag) * 2 segments
			'archive_numeric_2010_12' => (6 + 2 + 6) * 2,   
		
			// all "Range" records are in December
			'archive_blob_2011_01' => 0,
			'archive_numeric_2011_01' => 0,
		);
		foreach($tests as $table => $expectedRows)
		{
	        $sql = "SELECT count(*) FROM " . Piwik_Common::prefixTable($table) . " WHERE period = ".Piwik::$idPeriods['range'];
	        $countBlobs = Zend_Registry::get('db')->fetchOne($sql);
	        $this->assertEqual( $expectedRows, $countBlobs, $table);
		}
	}

	// test Metadata API + period=range&date=lastN
	function test_periodIsRange_dateIsLastN_MetadataAndNormalAPI()
	{
		if(date('G') == 23 || date('G') == 22) {
			echo "SKIPPED test_periodIsRange_dateIsLastN_MetadataAndNormalAPI() since it fails around midnight...";
			$this->pass();
			return; 
		}
		$dateTime = Piwik_Date::factory('now')->getDateTime();
        $idSite = $this->doTest_twoVisitsWithCustomVariables($dateTime);
        
		$this->setApiNotToCall(array());
        $this->setApiToCall(array(	'API.getProcessedReport',
        							'Actions.getPageUrls',
        							'Goals.get',
    	                            'CustomVariables.getCustomVariables',
        							'Referers.getCampaigns',
        							'Referers.getKeywords',
        							'VisitsSummary.get',
        							'Live',
    	));
    	$segments = array(
    		false,
    		'daysSinceFirstVisit!=50',
    		'visitorId!=33c31e01394bdc63',
    		// testing both filter on Actions table and visit table
    		'visitorId!=33c31e01394bdc63;daysSinceFirstVisit!=50',
    		//'pageUrl!=http://unknown/not/viewed',
    	);
    	$dates = array(
    		'last7',
    		Piwik_Date::factory('now')->subDay(6)->toString() . ',today',
    		Piwik_Date::factory('now')->subDay(6)->toString() . ',now',
    	);
    	foreach($segments as $segment)
    	{
	    	foreach($dates as $date)
	    	{
	    		$this->callGetApiCompareOutput(__FUNCTION__, 'xml', 
	        								$idSite, 
	        								$date, 
	        								$periods = array('range'), 
	        								$setDateLastN = false,
	        								$language=false, 
	        								$segment,
	        								// testing getLastVisitsForVisitor requires a visitor ID
	        								$this->visitorId
	        	);
	    	}
    	}
	}
	
	function test_PiwikTracker_trackForceUsingVisitId_insteadOfHeuristics_alsoTestsCampaignTracking()
	{
		$this->setApiToCall( array(
				'VisitsSummary.get', 
				'Referers.getCampaigns'
		));
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st page view
        $t->setUrl( 'http://example.org/index.htm?utm_campaign=GA Campaign&piwik_kwd=Piwik kwd&utm_term=GA keyword SHOULD NOT DISPLAY' );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        $visitorId = $t->getVisitorId();
        $this->assertTrue(strlen($visitorId) == 16);
        
        // Test setting/getting the first party cookie via the PHP Tracking Client 
        $_COOKIE['_pk_id_1_1fff'] = 'ca0afe7b6b692ff5.1302307497.1.1302307497.1302307497';
        $_COOKIE['_pk_ref_1_1fff'] = '["YEAH","RIGHT!",1302307497,"http://referrer.example.org/page/sub?query=test&test2=test3"]';
        $_COOKIE['_pk_cvar_1_1fff'] = '{"1":["VAR 1 set, var 2 not set","yes"],"3":["var 3 set","yes!!!!"]}';
        $this->assertTrue($t->getVisitorId() == 'ca0afe7b6b692ff5');
        $this->assertTrue($t->getAttributionInfo() == $_COOKIE['_pk_ref_1_1fff']);
        $this->assertTrue($t->getCustomVariable(1) == array("VAR 1 set, var 2 not set", "yes"));
        $this->assertTrue($t->getCustomVariable(2) == false);
        $this->assertTrue($t->getCustomVariable(3) == array("var 3 set", "yes!!!!"));
        $this->assertTrue($t->getCustomVariable(4) == false);
        $this->assertTrue($t->getCustomVariable(5) == false);
        $this->assertTrue($t->getCustomVariable(6) == false);
        $this->assertTrue($t->getCustomVariable(-1) == false);
        unset($_COOKIE['_pk_id_1_1fff']);
        unset($_COOKIE['_pk_ref_1_1fff']);
        unset($_COOKIE['_pk_cvar_1_1fff']);
        
        // Create a new Tracker object, with different attributes
        $t2 = $this->getTracker($idSite, $dateTime, $defaultInit = false);
        
        // Make sure the ID is different at first
        $visitorId2 = $t2->getVisitorId();
        $this->assertTrue($visitorId != $visitorId2);
        
        // Then force the visitor ID 
        $t2->setVisitorId($visitorId);
        
        // And Record a Goal: The previous visit should be updated rather than a new visit Created 
        $t2->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t2->doTrackGoal($idGoal, $revenue = 42.256));
        
        // TOTAL should be: 1 visit, 1 converted goal, 1 page view
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	
}

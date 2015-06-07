<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

// Tests Visits and Dimensions behavior which is a lot of logic so not in VisitTest

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visitor;
use Piwik\Piwik;
use Piwik\EventDispatcher;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class FakeTrackerVisitDimension1 extends VisitDimension
{
    protected $columnName  = 'custom_dimension_1';

    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return 'existing1';
    }
}

class FakeTrackerVisitDimension2 extends VisitDimension
{
    protected $columnName  = 'custom_dimension_2';

    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return 'onNew2';
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }

    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return false;
    }
}

class FakeTrackerVisitDimension3 extends VisitDimension
{
    protected $columnName  = 'custom_dimension_3';

    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return 'onNew3';
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return 'existing3';
    }

    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return 'converted3';
    }
}

class FakeTrackerVisitDimension4 extends VisitDimension
{
    protected $columnName  = 'custom_dimension_4';

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn('custom_dimension_3') . 'extended';
    }

    public function getRequiredFields()
    {
        return array('custom_dimension_3');
    }
}


class FakeTrackerVisit extends Visit
{
    public function __construct($request)
    {
        $this->request = $request;
        $this->visitorInfo['location_ip'] = $request->getIp();
        $this->visitorInfo['idvisitor']   = 1;
    }

    public function handleExistingVisit($visitor, $action, $visitIsConverted)
    {
        parent::handleExistingVisit($visitor, $action, $visitIsConverted);
    }

    public function handleNewVisit($visitor, $action, $visitIsConverted)
    {
        parent::handleNewVisit($visitor, $action, $visitIsConverted);
    }

    public function getAllVisitDimensions()
    {
        return array(
            new FakeTrackerVisitDimension1(),
            new FakeTrackerVisitDimension2(),
            new FakeTrackerVisitDimension3(),
            new FakeTrackerVisitDimension4(),
        );
    }

    public function getVisitorInfo()
    {
        return $this->visitorInfo;
    }

    protected function insertNewVisit($visit)
    {
    }

    protected function updateExistingVisit($valuesToUpdate)
    {
    }
}

/**
 * @group Core
 * @group VisitTest
 */
class Visit2Test extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        Fixture::createWebsite('2014-01-01 00:00:00');

        /** @var EventDispatcher $eventObserver */
        $eventObserver = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\EventDispatcher');
        $eventObserver->addObserver('Tracker.Request.getIdSite', function (&$idSite) {
            $idSite = 1;
        });
    }

    public function test_handleNewVisitWithoutConversion_shouldTriggerDimensions()
    {
        $request = new Request(array());
        $visitor = new Visitor($request, '');

        $visit = new FakeTrackerVisit($request);
        $visit->handleNewVisit($visitor, null, false);

        $info = $visit->getVisitorInfo();

        $this->assertEquals('onNew2', $info['custom_dimension_2']);
        $this->assertEquals('onNew3', $info['custom_dimension_3']);
        $this->assertArrayNotHasKey('custom_dimension_1', $info); // on new visit returns false and should be ignored
        $this->assertArrayNotHasKey('custom_dimension_4', $info); // on new visit not defined

        // make sure visitor gets updated as well
        $this->assertEquals('onNew2', $visitor->getVisitorColumn('custom_dimension_2'));
        $this->assertEquals('onNew3', $visitor->getVisitorColumn('custom_dimension_3'));
        $this->assertFalse($visitor->getVisitorColumn('custom_dimension_1'));
        $this->assertFalse($visitor->getVisitorColumn('custom_dimension_4'));
    }

    public function test_handleNewVisitWithConversion_shouldTriggerDimensions()
    {
        $request = new Request(array());
        $visitor = new Visitor($request, '');

        $visit = new FakeTrackerVisit($request);
        $visit->handleNewVisit($visitor, null, true);

        $info = $visit->getVisitorInfo();

        $this->assertEquals('onNew2', $info['custom_dimension_2']); // on converted visit returns false and should be ignored
        $this->assertEquals('converted3', $info['custom_dimension_3']); // a conversion should overwrite an existing value
        $this->assertArrayNotHasKey('custom_dimension_1', $info);
        $this->assertArrayNotHasKey('custom_dimension_4', $info);

        $this->assertEquals('converted3', $visitor->getVisitorColumn('custom_dimension_3'));
    }

    public function test_handleExistingVisitWithoutConversion_shouldTriggerDimensions()
    {
        $request = new Request(array());
        $visitor = new Visitor($request, '');

        $visit = new FakeTrackerVisit($request);
        $visit->handleNewVisit($visitor, null, false);
        $visit->handleExistingVisit($visitor, null, false);

        $info = $visit->getVisitorInfo();

        $this->assertEquals('existing1', $info['custom_dimension_1']);
        $this->assertEquals('onNew2', $info['custom_dimension_2']);  // on existing visit returns false and should be ignored/ not overwrite on new value
        $this->assertEquals('existing3', $info['custom_dimension_3']);
        $this->assertEquals('existing3extended', $info['custom_dimension_4']); // accesses a previously set column

        // make sure visitor gets updated as well
        $this->assertEquals('existing1', $visitor->getVisitorColumn('custom_dimension_1'));
        $this->assertEquals('onNew2', $visitor->getVisitorColumn('custom_dimension_2'));
        $this->assertEquals('existing3', $visitor->getVisitorColumn('custom_dimension_3'));
        $this->assertEquals('existing3extended', $visitor->getVisitorColumn('custom_dimension_4'));
    }

    public function test_handleExistingVisitWithConversion_shouldTriggerDimensions()
    {
        $request = new Request(array());
        $visitor = new Visitor($request, '');

        $visit = new FakeTrackerVisit($request);
        $visit->handleNewVisit($visitor, null, false);
        $visit->handleExistingVisit($visitor, null, true);

        $info = $visit->getVisitorInfo();

        $this->assertEquals('existing1', $info['custom_dimension_1']);
        $this->assertEquals('onNew2', $info['custom_dimension_2']); // on converted visit returns false and should be ignored
        $this->assertEquals('converted3', $info['custom_dimension_3']); // a conversion should overwrite an existing value
        $this->assertEquals('existing3extended', $info['custom_dimension_4']);

        $this->assertEquals('converted3', $visitor->getVisitorColumn('custom_dimension_3'));
    }
}

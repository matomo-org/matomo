<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

// Tests Visits and Dimensions behavior which is a lot of logic so not in VisitTest

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visitor;
use Piwik\Piwik;
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
    public function __construct($request, Visit\VisitProperties $visitProperties)
    {
        parent::__construct();

        $this->request = $request;
        $this->visitProperties = $visitProperties;
        $this->visitProperties->setProperty('location_ip', $request->getIp());
        $this->visitProperties->setProperty('idvisitor', 1);
    }

    public function handleExistingVisit($visitIsConverted)
    {
        parent::handleExistingVisit($visitIsConverted);
    }

    public function handleNewVisit($visitIsConverted)
    {
        parent::handleNewVisit($visitIsConverted);
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
        return $this->visitProperties->getProperties();
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
    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2014-01-01 00:00:00');
        Piwik::addAction('Tracker.Request.getIdSite', function (&$idSite) {
            $idSite = 1;
        });
    }

    public function test_handleNewVisitWithoutConversion_shouldTriggerDimensions()
    {
        $request = new Request(array());
        $visitProperties = new Visit\VisitProperties();
        $visitor = new Visitor($visitProperties);

        $visit = new FakeTrackerVisit($request, $visitProperties);
        $visit->handleNewVisit(false);

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
        $visitProperties = new Visit\VisitProperties();
        $visitor = new Visitor($visitProperties);

        $visit = new FakeTrackerVisit($request, $visitProperties);
        $visit->handleNewVisit(true);

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
        $visitProperties = new Visit\VisitProperties();
        $visitor = new Visitor($visitProperties);

        $visit = new FakeTrackerVisit($request, $visitProperties);
        $visit->handleNewVisit(false);
        $visit->handleExistingVisit(false);

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
        $visitProperties = new Visit\VisitProperties();
        $visitor = new Visitor($visitProperties);

        $visit = new FakeTrackerVisit($request, $visitProperties);
        $visit->handleNewVisit(false);
        $visit->handleExistingVisit(true);

        $info = $visit->getVisitorInfo();

        $this->assertEquals('existing1', $info['custom_dimension_1']);
        $this->assertEquals('onNew2', $info['custom_dimension_2']); // on converted visit returns false and should be ignored
        $this->assertEquals('converted3', $info['custom_dimension_3']); // a conversion should overwrite an existing value
        $this->assertEquals('existing3extended', $info['custom_dimension_4']);

        $this->assertEquals('converted3', $visitor->getVisitorColumn('custom_dimension_3'));
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Tracker;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Config;
use Piwik\Date;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * @group CoreHome
 * @group CoreHome_Integration
 */
class VisitRequestProcessorTest extends IntegrationTestCase
{
    public function testIsVisitNewReturnsTrueIfTrackerAlwaysNewVisitorIsSet()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-02 08:08:34',
            $thisActionTime = '2012-01-02 08:12:45',
            $isVisitorKnown = true,
            [ 'trackerAlwaysNewVisitor' => true ]
        );

        $result = $visit->isVisitNew($visitProperties, $request, null);

        $this->assertTrue($result);
    }

    public function testIsVisitNewReturnsTrueIfNewVisitQueryParamIsSet()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-02 08:08:34',
            $thisActionTime = '2012-01-02 08:12:45',
            $isVisitorKnown = true,
            [],
            ['new_visit' => '1']
        );

        $result = $visit->isVisitNew($visitProperties, $request, null);

        $this->assertTrue($result);
    }

    public function testIsVisitNewReturnsFalseIfLastActionTimestampIsWithinVisitTimeLengthAndNoDimensionForcesVisitAndVisitorKnown()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-02 08:08:34',
            $thisActionTime = '2012-01-02 08:12:45',
            $isVisitorKnown = true
        );

        $result = $visit->isVisitNew($visitProperties, $request, null);

        $this->assertFalse($result);
    }

    public function testIsVisitNewReturnsTrueIfLastActionTimestampWasYesterday()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        // test same day
        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-01 23:59:58',
            $thisActionTime = '2012-01-01 23:59:59',
            $isVisitorKnown = true
        );
        $result = $visit->isVisitNew($visitProperties, $request, null);
        $this->assertFalse($result);

        // test different day
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-01 23:59:58',
            $thisActionTime = '2012-01-02 00:00:01',
            $isVisitorKnown = true
        );
        $result = $visit->isVisitNew($visitProperties, $request, null);
        $this->assertTrue($result);
    }


    public function testIsVisitNewReturnsTrueIfLastActionTimestampIsNotWithinVisitTimeLengthAndNoDimensionForcesVisitAndVisitorNotKnown()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction($lastActionTime = '2012-01-02 08:08:34', $thisActionTime = '2012-01-02 09:12:45');

        $result = $visit->isVisitNew($visitProperties, $request, null);

        $this->assertTrue($result);
    }

    public function testIsVisitNewReturnsTrueIfLastActionTimestampIsWithinVisitTimeLengthAndDimensionForcesVisit()
    {
        $this->setDimensionsWithOnNewVisit([false, false, true]);

        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction($lastActionTime = '2012-01-02 08:08:34', $thisActionTime = '2012-01-02 08:12:45');

        $result = $visit->isVisitNew($visitProperties, $request, null);

        $this->assertTrue($result);
    }

    public function testIsVisitNewReturnsTrueIfDimensionForcesVisitAndVisitorKnown()
    {
        $this->setDimensionsWithOnNewVisit([false, false, true]);

        /** @var VisitRequestProcessor $visit */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction($lastActionTime = '2012-01-02 08:08:34', $thisActionTime = '2012-01-02 08:12:45');

        $result = $visit->isVisitNew($visitProperties, $request, null);

        $this->assertTrue($result);
    }

    public function testIsVisitNewReturnsTrueWhenUserIdChanges()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        /** @var VisitRequestProcessor $visit */
        /** @var Request $request */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-02 08:08:34',
            $thisActionTime = '2012-01-02 08:12:45',
            $isVisitorKnown = true
        );

        $visitProperties->setProperty('user_id', 'foo_different');
        $request->setParam('uid', 'foo');
        $result = $visit->isVisitNew($visitProperties, $request, null);
        $this->assertTrue($result);
    }

    public function testIsVisitNewReturnsTrueWhenUserChangesAndUserIdNotOverwritesVisitorId()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['enable_userid_overwrites_visitorid'] = 0;
        $config->Tracker = $tracker;

        /** @var VisitRequestProcessor $visit */
        /** @var VisitProperties $visitProperties */
        /** @var Request $request */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-02 08:08:34',
            $thisActionTime = '2012-01-02 08:12:45',
            $isVisitorKnown = true
        );

        $visitProperties->setProperty('user_id', 'foo_different');
        $request->setParam('uid', 'foo');
        $result = $visit->isVisitNew($visitProperties, $request, null);
        $this->assertTrue($result);
    }

    private function makeVisitorAndAction(
        $lastActionTimestamp,
        $currentActionTime,
        $isVisitorKnown = false,
        $processorParams = [],
        $extraRequestParams = []
    ) {
        $idsite = API::getInstance()->addSite("name", "http://piwik.net/");

        /** @var Request $request */
        [$visit, $request] = $this->prepareVisitWithRequest(
            array_merge(['idsite' => $idsite], $extraRequestParams),
            $currentActionTime,
            $processorParams
        );

        $visitProperties = new VisitProperties();
        $visitProperties->setProperty('visit_last_action_time', Date::factory($lastActionTimestamp)->getTimestamp());
        $request->setMetadata('CoreHome', 'isVisitorKnown', $isVisitorKnown);

        return [$visit, $visitProperties, $request];
    }

    private function setDimensionsWithOnNewVisit($dimensionOnNewVisitResults)
    {
        $dimensions = [];
        foreach ($dimensionOnNewVisitResults as $onNewVisitResult) {
            $dim = $this->getMockBuilder(VisitDimension::class)
                ->onlyMethods(['shouldForceNewVisit', 'getColumnName'])
                        ->getMock();
            $dim->expects($this->any())->method('shouldForceNewVisit')->will($this->returnValue($onNewVisitResult));
            $dimensions[] = $dim;
        }

        $cache = Cache::getTransientCache();
        $cache->save(CacheId::pluginAware('VisitDimensions'), $dimensions);
        Visit::$dimensions = null;
    }

    private function prepareVisitWithRequest($requestParams, $requestDate, $params = [])
    {
        $request = new Request($requestParams);
        $request->setCurrentTimestamp(Date::factory($requestDate)->getTimestamp());

        $visit = self::$fixture->piwikEnvironment->getContainer()->make('Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor', $params);

        return [$visit, $request];
    }
}

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

    public function testIsVisitNewStillWorksWhenAPluginOverridesTheUserIdCheck()
    {
        $this->setDimensionsWithOnNewVisit([false, false, false]);

        /** @var LegacyOverrideVisitRequestProcessor $visit */
        /** @var VisitProperties $visitProperties */
        /** @var Request $request */
        [$visit, $visitProperties, $request] = $this->makeVisitorAndAction(
            $lastActionTime = '2012-01-02 08:08:34',
            $thisActionTime = '2012-01-02 08:12:45',
            $isVisitorKnown = true,
            [],
            [],
            LegacyOverrideVisitRequestProcessor::class
        );

        $visitProperties->setProperty('user_id', 'foo_different');
        $request->setParam('uid', 'foo');

        $this->assertTrue($visit->isVisitNew($visitProperties, $request, null));
        $this->assertTrue($visit->lastUserIdWasChecked, 'the plugin override was not called');
    }

    private function makeVisitorAndAction(
        $lastActionTimestamp,
        $currentActionTime,
        $isVisitorKnown = false,
        $processorParams = [],
        $extraRequestParams = [],
        $processorClass = VisitRequestProcessor::class
    ) {
        $idsite = API::getInstance()->addSite("name", "http://piwik.net/");

        /** @var Request $request */
        [$visit, $request] = $this->prepareVisitWithRequest(
            array_merge(['idsite' => $idsite], $extraRequestParams),
            $currentActionTime,
            $processorParams,
            $processorClass
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

    private function prepareVisitWithRequest(
        $requestParams,
        $requestDate,
        $params = [],
        $processorClass = VisitRequestProcessor::class
    ) {
        $request = new Request($requestParams);
        $request->setCurrentTimestamp(Date::factory($requestDate)->getTimestamp());

        if ($processorClass !== VisitRequestProcessor::class) {
            // the container binds these two for the processor itself, not for a subclass of it
            $params += [
                'visitStandardLength' => Config::getInstance()->Tracker['visit_standard_length'],
                'trackerAlwaysNewVisitor' => false,
            ];
        }

        $visit = self::$fixture->piwikEnvironment->getContainer()->make($processorClass, $params);

        return [$visit, $request];
    }
}

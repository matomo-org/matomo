<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\System;

use Piwik\Config;
use Piwik\Date;
use Piwik\Log\Logger;
use Piwik\Log\LoggerInterface;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use MatomoTracker;

/**
 * @group Monolog
 * @group TrackerLoggingTest
 * @group Plugins
 */
class TrackerLoggingTest extends SystemTestCase
{
    private $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    public function test_shouldReturnDebugOutput_IfDebugIsEnabled()
    {
        $this->setTrackerConfig(array('debug' => '1'));

        $tracker = $this->buildTracker();
        $this->assertTrackerResponseContainsLogOutput($tracker);
    }

    public function test_shouldReturnDebugOutput_IfDebugOnDemandIsEnabled()
    {
        $this->setTrackerConfig(array('debug_on_demand' => '1', 'debug' => 0));

        $tracker = $this->buildTracker();
        $tracker->setDebugStringAppend('debug=1');
        $this->assertTrackerResponseContainsLogOutput($tracker);
    }

    public function test_shouldNotReturnDebugOutput_IfDebugOnDemandIsDisabled()
    {
        $this->setTrackerConfig(array('debug_on_demand' => '0', 'debug' => 0));

        $tracker = $this->buildTracker();
        $tracker->setDebugStringAppend('debug=1');

        Fixture::checkResponse($tracker->doTrackPageView('incredible title!'));
    }

    private function buildTracker()
    {
        $t = Fixture::getTracker($this->idSite, Date::factory('2014-01-05 00:01:01')->getDatetime());
        $t->setDebugStringAppend('debug=1');
        $t->setTokenAuth(Fixture::getTokenAuth());
        $t->setUrl('http://example.org/index1.htm');

        return $t;
    }

    private function assertTrackerResponseContainsLogOutput(MatomoTracker $t)
    {
        $response = $t->doTrackPageView('incredible title!');

        $this->assertRegExp('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] ' . preg_quote("piwik.DEBUG: Debug enabled - Input parameters: array (   'idsite' => '1',   'rec' => '1',   'apiv' => '1',") . "/", $response);
    }

    private function setTrackerConfig($trackerConfig)
    {
        $testingEnvironment = new TestingEnvironmentVariables();
        $testingEnvironment->overrideConfig('Tracker', $trackerConfig);
        $testingEnvironment->overrideConfig('log', 'log_writers', array('screen'));
        $testingEnvironment->save();
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            LoggerInterface::class => \Piwik\DI::get(Logger::class),
            Config::class => \Piwik\DI::decorate(function (Config $config) {
                $config->tests['enable_logging'] = 1;
                $config->log['log_writers'] = ['screen'];
                return $config;
            }),
            'Tests.log.allowAllHandlers' => true,
        );
    }
}

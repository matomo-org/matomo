<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Matomo\Network\IP;
use Piwik\Common;
use Piwik\Date;
use Piwik\EventDispatcher;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\VisitorRecognizer;

/**
 * @group Core
 */
class VisitorRecognizerTest extends IntegrationTestCase
{
    /**
     * @var VisitorRecognizer
     */
    private $recognizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->recognizer = new VisitorRecognizer(
            true,
            1800,
            24000,
            new Model(),
            EventDispatcher::getInstance()
        );

        Fixture::createWebsite('2020-01-01 02:03:04');
    }

    public function testFindKnownVisitorWhenNotExceededMaxActionsLimitFindsVisitor()
    {
        $this->assertNull($this->recognizer->getLastKnownVisit());

        $configId = $this->createVisit(9999);
        $visitor = $this->findKnownVisitor($configId);
        $this->assertTrue($visitor);
        $this->assertNotEmpty($this->recognizer->getLastKnownVisit());
    }

    private function findKnownVisitor($configId)
    {
        $visitProperties = new VisitProperties();
        $request = new Request(['idsite' => 1, 'cid' => $configId, 'uid' => $configId]);

        return $this->recognizer->findKnownVisitor($configId, $visitProperties, $request);
    }

    private function createVisit($maxTotalActions)
    {
        $configId = '1234567812345678';
        $request = new Request(['idsite' => 1, 'uid' => $configId]);
        $model = new Model();
        $model->createVisit(array(
            'config_id' => Common::hex2bin($configId),
            'idsite' => 1,
            'user_id' => $configId,
            'visit_total_time' => 1,
            'visit_total_actions' => $maxTotalActions,
            'visit_last_action_time' => Date::now()->getDatetime(),
            'visit_first_action_time' => Date::now()->getDatetime(),
            'idvisitor' => $request->getVisitorId(),
            'location_ip' => IP::fromStringIP('1.1.1.1')->toBinary()
        ));

        return $configId;
    }

    public function testFindKnownVisitorWhenExceededMaxActionsLimitFindsNotVisitor()
    {
        $configId = $this->createVisit(10000);
        $visitor = $this->findKnownVisitor($configId);
        $this->assertFalse($visitor);
        $this->assertFalse($this->recognizer->getLastKnownVisit());
    }

    public function testRemoveUnchangedValuesNewVisitShouldNotChangeAnything()
    {
        $visit = array(
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
            'foo' => 'bar',
        );
        $result = $this->recognizer->removeUnchangedValues($visit);

        $this->assertEquals($visit, $result);
    }

    public function testRemoveUnchangedValuesExistingVisitWithDifferentValuesShouldNotChangeAnything()
    {
        $visit = array(
            'idvisitor' => Common::hex2bin('1234567890234567'),
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
            'foo' => 'bar',
        );
        $originalProperties = new VisitProperties(array(
            'visit_last_action_time' => '2020-05-05 04:05:05',
            'visit_total_time' => '40',
        ));
        $result = $this->recognizer->removeUnchangedValues($visit, $originalProperties);

        $this->assertEquals($visit, $result);
    }

    public function testRemoveUnchangedValuesExistingVisitWithSomeSameValuesShouldRemoveUnchangedValues()
    {
        $visit = array(
            'idvisitor' => Common::hex2bin('1234567890234569'),
            'user_id' => 'hello',
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
            'foo' => 'bar',
        );
        $originalVisit = new VisitProperties(array(
            'idvisitor' => Common::hex2bin('1234567890234567'),
            'user_id' => 'hello',
            'visit_last_action_time' => '2020-05-05 04:05:05',
            'visit_total_time' => '50',
        ));
        $result = $this->recognizer->removeUnchangedValues($visit, $originalVisit);

        $this->assertEquals(array(
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'foo' => 'bar',
            'idvisitor' => Common::hex2bin('1234567890234569'),
        ), $result);
    }

    public function testRemoveUnchangedValuesExistingVisitWithAllSameValuesShouldRemoveEmptyArray()
    {
        $visit = array(
            'idvisitor' => Common::hex2bin('1234567890234569'),
            'user_id' => 'hello',
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
        );
        $originalVisit = new VisitProperties($visit);
        $result = $this->recognizer->removeUnchangedValues($visit, $originalVisit);

        $this->assertEquals(array(), $result);
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class NumericAttributeGoalTrackingTest extends IntegrationTestCase
{
    private $idSite = 1;
    private $visitDurationIdGoal = 1;

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        $idSite = Fixture::createWebsite('2012-02-03 04:05:56');
        API::getInstance()->addGoal($idSite, 'visit duration goal', 'visit_duration', 2.5, 'greater_than');
    }

    public function testTrackingVisitDurationGoal()
    {
        $t = Fixture::getTracker($this->idSite, '2013-02-03 04:00:00');
        $this->assertEquals(0, $this->getConversionCount($this->visitDurationIdGoal));

        Fixture::checkResponse($t->doTrackPageView('test page view'));
        $this->assertEquals(0, $this->getConversionCount($this->visitDurationIdGoal));

        $t->setForceVisitDateTime('2013-02-03 04:02:00');
        Fixture::checkResponse($t->doTrackPageView('another page view'));
        $this->assertEquals(0, $this->getConversionCount($this->visitDurationIdGoal));

        $t->setForceVisitDateTime('2013-02-03 04:02:29');
        Fixture::checkResponse($t->doTrackEvent('event category', 'blah', 'blah'));
        $this->assertEquals(0, $this->getConversionCount($this->visitDurationIdGoal));

        $t->setForceVisitDateTime('2013-02-03 04:02:31');
        Fixture::checkResponse($t->doTrackEvent('event category', 'blah 2', 'blah 2'));
        $this->assertEquals(1, $this->getConversionCount($this->visitDurationIdGoal));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function getConversionCount($idGoal)
    {
        return Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_conversion') . ' WHERE idsite = ? AND idgoal = ?',
            [$this->idSite, $idGoal]
        );
    }
}

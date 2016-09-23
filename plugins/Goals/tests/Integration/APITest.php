<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Piwik;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Goals
 * @group Plugins
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    private $idSite = 1;

    public function setUp()
    {
        parent::setUp();
        $this->api = API::getInstance();

        Fixture::createAccessInstance();
        Piwik::setUserHasSuperUserAccess();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    public function test_addGoal_shouldReturnGoalId_IfCreationIsSuccessful()
    {
        $idGoal = $this->createAnyGoal();

        $this->assertSame(1, $idGoal);
    }

    public function test_addGoal_shouldSucceed_IfOnlyMinimumFieldsGiven()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'url', 'http://www.test.de/?pk_campaign=1', 'exact', false, false, false, 'test description');

        $this->assertGoal($idGoal, 'MyName', 'test description', 'url', 'http://www.test.de/?pk_campaign=1', 'exact', 0, 0, 0);
    }

    public function test_addGoal_ShouldSucceed_IfAllFieldsGiven()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'url', 'http://www.test.de', 'exact', true, 50, true);

        $this->assertGoal($idGoal, 'MyName', '', 'url', 'http://www.test.de', 'exact', 1, 50, 1);
    }

    public function test_addGoal_ShouldSucceed_IfExactPageTitle()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'title', 'normal title', 'exact', true, 50, true);

        $this->assertGoal($idGoal, 'MyName', '', 'title', 'normal title', 'exact', 1, 50, 1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Goals_ExceptionInvalidMatchingString
     */
    public function test_addGoal_shouldThrowException_IfPatternTypeIsExactAndMatchAttributeNotEvent()
    {
        $this->api->addGoal($this->idSite, 'MyName', 'url', 'www.test.de', 'exact');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Goals_ExceptionInvalidMatchingString
     */
    public function test_addGoal_shouldThrowException_IfPatternTypeIsExactAndMatchAttributeNotEvent2()
    {
        $this->api->addGoal($this->idSite, 'MyName', 'external_website', 'www.test.de', 'exact');
    }

    public function test_addGoal_shouldNotThrowException_IfPatternTypeIsExactAndMatchAttributeIsEvent()
    {
        $this->api->addGoal($this->idSite, 'MyName1', 'event_action', 'test', 'exact');
        $this->api->addGoal($this->idSite, 'MyName2', 'event_name', 'test', 'exact');
        $idGoal = $this->api->addGoal($this->idSite, 'MyName3', 'event_category', 'test', 'exact');

        $this->assertSame('3', (string)$idGoal);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasAdminAccess Fake exception
     */
    public function test_addGoal_shouldThrowException_IfNotEnoughPermission()
    {
        $this->setNonAdminUser();
        $this->createAnyGoal();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasAdminAccess Fake exception
     */
    public function test_updateGoal_shouldThrowException_IfNotEnoughPermission()
    {
        $idGoal = $this->createAnyGoal();
        $this->assertSame(1, $idGoal); // make sure goal is created and does not already fail here
        $this->setNonAdminUser();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'url', 'www.test.de', 'exact');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Goals_ExceptionInvalidMatchingString
     */
    public function test_updateGoal_shouldThrowException_IfPatternTypeIsExactAndMatchAttributeNotEvent()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'url', 'www.test.de', 'exact');
    }

    public function test_updateGoal_shouldNotThrowException_IfPatternTypeIsExactAndMatchAttributeIsEvent()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'event_action', 'www.test.de', 'exact');
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'event_category', 'www.test.de', 'exact');
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'event_name', 'www.test.de', 'exact');

        $this->assertSame(1, $idGoal);
    }

    public function test_updateGoal_shouldUpdateAllGivenFields()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains', true, 999, true);

        $this->assertGoal($idGoal, 'UpdatedName', '', 'file', 'http://www.updatetest.de', 'contains', 1, 999, 1);
    }

    public function test_updateGoal_shouldUpdateMinimalFields_ShouldLeaveOtherFieldsUntouched()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains');

        $this->assertGoal($idGoal, 'UpdatedName', '', 'file', 'http://www.updatetest.de', 'contains', 0, 0, 0);
    }

    public function test_deleteGoal_shouldNotDeleteAGoal_IfGoalIdDoesNotExist()
    {
        $this->assertHasNoGoals();

        $this->createAnyGoal();
        $this->assertHasGoals();

        $this->api->deleteGoal($this->idSite, 999);
        $this->assertHasGoals();
    }

    public function test_deleteGoal_shouldNotDeleteAGoal_IfSiteDoesNotMatchGoalId()
    {
        $this->assertHasNoGoals();

        $idGoal = $this->createAnyGoal();
        $this->assertHasGoals();

        $this->api->deleteGoal($idSite = 2, $idGoal);
        $this->assertHasGoals();
    }

    public function test_deleteGoal_shouldDeleteAGoal_IfGoalAndSiteMatches()
    {
        $this->assertHasNoGoals();

        $idGoal = $this->createAnyGoal();
        $this->assertHasGoals();

        $this->api->deleteGoal($this->idSite, $idGoal);
        $this->assertHasNoGoals();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasViewAccess Fake exception
     */
    public function test_getGoal_shouldThrowException_IfNotEnoughPermission()
    {
        $idGoal = $this->createAnyGoal();
        $this->assertSame(1, $idGoal);
        $this->setNonAdminUser();
        $this->api->getGoal($this->idSite, $idGoal);
    }

    public function test_getGoal_shouldReturnNullIfItDoesNotExist()
    {
        $this->assertNull($this->api->getGoal($this->idSite, $idGoal = 99));
    }

    public function test_getGoal_shouldReturnExistingGoal()
    {
        $idGoal = $this->createAnyGoal();
        $this->assertSame(1, $idGoal);
        $goal = $this->api->getGoal($this->idSite, $idGoal);
        $this->assertEquals(array(
            'idsite' => '1',
            'idgoal' => '1',
            'name' => 'MyName1',
            'description' => '',
            'match_attribute' => 'event_action',
            'pattern' => 'test',
            'pattern_type' => 'exact',
            'case_sensitive' => '0',
            'allow_multiple' => '0',
            'revenue' => '0',
            'deleted' => '0',
        ), $goal);
    }

    private function assertHasGoals()
    {
        $goals = $this->getGoals();
        $this->assertNotEmpty($goals);
    }

    private function assertHasNoGoals()
    {
        $goals = $this->getGoals();
        $this->assertEmpty($goals);
    }

    private function assertGoal($idGoal, $name, $description, $url, $pattern, $patternType, $caseSenstive = 0, $revenue = 0, $allowMultiple = 0)
    {
        $expected = array($idGoal => array(
            'idsite' => $this->idSite,
            'idgoal' => $idGoal,
            'name' => $name,
            'description' => $description,
            'match_attribute' => $url,
            'pattern' => $pattern,
            'pattern_type' => $patternType,
            'case_sensitive' => $caseSenstive,
            'allow_multiple' => $allowMultiple,
            'revenue' => $revenue,
            'deleted' => 0
        ));

        $goals = $this->getGoals();

        $this->assertEquals($expected, $goals);
    }

    private function getGoals()
    {
        return $this->api->getGoals($this->idSite);
    }

    private function createAnyGoal()
    {
        return $this->api->addGoal($this->idSite, 'MyName1', 'event_action', 'test', 'exact');
    }

    protected function setNonAdminUser()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = array(99);
        FakeAccess::$identity = 'aUser';
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}

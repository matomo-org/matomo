<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

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

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    /**
     * @dataProvider getTestDataForNumericMatchAttribute
     */
    public function test_addGoal_handlesAppropriatePatternTypesForNumericAttributes($matchAttribute, $patternType, $pattern, $expectException)
    {
        if ($expectException) {
            $this->expectException(\Exception::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->api->addGoal($this->idSite, 'test goal', $matchAttribute, $pattern, $patternType);
    }

    public function getTestDataForNumericMatchAttribute()
    {
        return [
            ['visit_duration', 'greater_than', 2, false],
            ['visit_duration', '>=', 2, true],
            ['visit_duration', 'exact', 2, true],
        ];
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
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'url', 'http://www.test.de', 'exact', true, 50, true, 'desc', true);

        $this->assertGoal($idGoal, 'MyName', 'desc', 'url', 'http://www.test.de', 'exact', 1, 50, 1, 1);
    }

    public function test_addGoal_ShouldSucceed_IfExactPageTitle()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'title', 'normal title', 'exact', true, 50, true);

        $this->assertGoal($idGoal, 'MyName', '', 'title', 'normal title', 'exact', 1, 50, 1);
    }

    public function test_addGoal_ShouldSucceed_IfRegexPageTitle()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'title', 'rere(.*)', 'regex', true, 50, true);

        $this->assertGoal($idGoal, 'MyName', '', 'title', 'rere(.*)', 'regex', 1, 50, 1);
    }

    public function test_addGoal_shouldThrowException_IfPatternTypeIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorXNotWhitelisted');

        $this->api->addGoal($this->idSite, 'MyName', 'external_website', 'www.test.de', 'invalid');
    }

    public function test_addGoal_shouldThrowException_IfPatternRegexIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorNoValidRegex');

        $this->api->addGoal($this->idSite, 'MyName', 'url', '/(%$f', 'regex');
    }

    public function test_addGoal_shouldThrowException_IfPatternTypeIsExactAndMatchAttributeNotEvent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Goals_ExceptionInvalidMatchingString');

        $this->api->addGoal($this->idSite, 'MyName', 'url', 'www.test.de', 'exact');
    }

    public function test_addGoal_shouldThrowException_IfPatternTypeIsExactAndMatchAttributeNotEvent2()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Goals_ExceptionInvalidMatchingString');

        $this->api->addGoal($this->idSite, 'MyName', 'external_website', 'www.test.de', 'exact');
    }

    public function test_addGoal_shouldNotThrowException_IfPatternTypeIsExactAndMatchAttributeIsEvent()
    {
        $this->api->addGoal($this->idSite, 'MyName1', 'event_action', 'test', 'exact');
        $this->api->addGoal($this->idSite, 'MyName2', 'event_name', 'test', 'exact');
        $idGoal = $this->api->addGoal($this->idSite, 'MyName3', 'event_category', 'test', 'exact');

        $this->assertSame('3', (string)$idGoal);
    }

    public function test_addGoal_shouldThrowException_IfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasWriteAccess Fake exception');

        $this->setNonAdminUser();
        $this->createAnyGoal();
    }

    public function test_updateGoal_shouldThrowException_IfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasWriteAccess Fake exception');

        $idGoal = $this->createAnyGoal();
        $this->assertSame(1, $idGoal); // make sure goal is created and does not already fail here
        $this->setNonAdminUser();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'url', 'www.test.de', 'exact');
    }

    public function test_updateGoal_shouldThrowException_IfPatternTypeIsExactAndMatchAttributeNotEvent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Goals_ExceptionInvalidMatchingString');

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

    public function test_getGoal_shouldThrowException_IfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('heckUserHasViewAccess Fake exception');

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
            'event_value_as_revenue' => '0',
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

    private function assertGoal(
        $idGoal,
        $name,
        $description,
        $url,
        $pattern,
        $patternType,
        $caseSenstive = 0,
        $revenue = 0,
        $allowMultiple = 0,
        $eventAsRevenue = 0
    ) {
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
            'deleted' => 0,
            'event_value_as_revenue' => $eventAsRevenue,
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

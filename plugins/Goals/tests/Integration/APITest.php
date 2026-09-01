<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\GoalManager;

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

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var int
     */
    private $idSiteTwo;

    /**
     * @var int
     */
    private $idVisit = 0;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();

        $this->idSite = Fixture::createWebsite('2014-01-01 00:00:00');
        $this->idSiteTwo = Fixture::createWebsite('2014-01-01 00:00:00');
    }

    /**
     * @dataProvider getTestDataForNumericMatchAttribute
     */
    public function testAddGoalHandlesAppropriatePatternTypesForNumericAttributes($matchAttribute, $patternType, $pattern, $expectException)
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

    public function testAddGoalShouldReturnGoalIdIfCreationIsSuccessful()
    {
        $idGoal = $this->createAnyGoal();

        $this->assertSame(1, $idGoal);
    }

    public function testAddGoalShouldSucceedIfOnlyMinimumFieldsGiven()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'url', 'http://www.test.de/?pk_campaign=1', 'exact', false, false, false, 'test description');

        $this->assertGoal($idGoal, 'MyName', 'test description', 'url', 'http://www.test.de/?pk_campaign=1', 'exact', 0, 0, 0);
    }

    public function testAddGoalShouldSucceedIfAllFieldsGiven()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'event_action', 'test', 'exact', true, 50, true, 'desc', true);

        $this->assertGoal($idGoal, 'MyName', 'desc', 'event_action', 'test', 'exact', 1, 50, 1, 1);
    }

    public function testAddGoalShouldThrowIfEventValueAsRevenueIsUsedForNonEventGoal()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("'useEventValueAsRevenue' can only be 1 if the goal matches an event attribute.");

        $this->api->addGoal($this->idSite, 'MyName', 'url', 'http://www.test.de', 'exact', false, false, false, '', true);
    }

    public function testUpdateGoalShouldThrowIfEventValueAsRevenueIsUsedForNonEventGoal()
    {
        $idGoal = $this->createAnyGoal();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("'useEventValueAsRevenue' can only be 1 if the goal matches an event attribute.");

        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'url', 'http://www.test.de', 'exact', false, false, false, '', true);
    }

    public function testAddGoalShouldSucceedIfExactPageTitle()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'title', 'normal title', 'exact', true, 50, true);

        $this->assertGoal($idGoal, 'MyName', '', 'title', 'normal title', 'exact', 1, 50, 1);
    }

    public function testAddGoalShouldSucceedIfRegexPageTitle()
    {
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'title', 'rere(.*)', 'regex', true, 50, true);

        $this->assertGoal($idGoal, 'MyName', '', 'title', 'rere(.*)', 'regex', 1, 50, 1);
    }

    public function testAddGoalShouldPreservePlusCharacterInRegexPattern()
    {
        // a "+" must not be turned into a space when saving a goal
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'url', '\/one_\d+\.php$', 'regex');

        $this->assertGoal($idGoal, 'MyName', '', 'url', '\/one_\d+\.php$', 'regex');
    }

    public function testAddGoalShouldPreservePlusCharacterInNameAndDescription()
    {
        // a "+" must not be turned into a space when saving a goal
        $idGoal = $this->api->addGoal($this->idSite, 'My + Name', 'title', 'a+b', 'contains', false, false, false, 'desc + text');

        $this->assertGoal($idGoal, 'My + Name', 'desc + text', 'title', 'a+b', 'contains');
    }

    public function testUpdateGoalShouldPreservePlusCharacterInRegexPattern()
    {
        // a "+" must not be turned into a space when updating a goal
        $idGoal = $this->api->addGoal($this->idSite, 'MyName', 'title', 'rere(.*)', 'regex');

        $this->api->updateGoal($this->idSite, $idGoal, 'My + Name', 'title', '(?!Postuler$).+', 'regex', false, false, false, 'desc + text');

        $this->assertGoal($idGoal, 'My + Name', 'desc + text', 'title', '(?!Postuler$).+', 'regex');
    }

    public function testAddGoalShouldThrowExceptionIfPatternTypeIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorXNotWhitelisted');

        $this->api->addGoal($this->idSite, 'MyName', 'external_website', 'www.test.de', 'invalid');
    }

    public function testAddGoalShouldThrowExceptionIfPatternIsEmptyForExternalWebsite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_PleaseSpecifyValue');

        $this->api->addGoal($this->idSite, 'MyName', 'external_website', '', 'contains');
    }

    public function testAddGoalShouldThrowExceptionIfPatternRegexIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorNoValidRegex');

        $this->api->addGoal($this->idSite, 'MyName', 'url', '/(%$f', 'regex');
    }

    public function testAddGoalShouldThrowExceptionIfPatternTypeIsExactAndMatchAttributeNotEvent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Goals_ExceptionInvalidMatchingString');

        $this->api->addGoal($this->idSite, 'MyName', 'url', 'www.test.de', 'exact');
    }

    public function testAddGoalShouldThrowExceptionIfPatternTypeIsExactAndMatchAttributeNotEvent2()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Goals_ExceptionInvalidMatchingString');

        $this->api->addGoal($this->idSite, 'MyName', 'external_website', 'www.test.de', 'exact');
    }

    public function testAddGoalShouldNotThrowExceptionIfPatternTypeIsExactAndMatchAttributeIsEvent()
    {
        $this->api->addGoal($this->idSite, 'MyName1', 'event_action', 'test', 'exact');
        $this->api->addGoal($this->idSite, 'MyName2', 'event_name', 'test', 'exact');
        $idGoal = $this->api->addGoal($this->idSite, 'MyName3', 'event_category', 'test', 'exact');

        $this->assertSame('3', (string)$idGoal);
    }

    public function testAddGoalShouldThrowExceptionIfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasWriteAccess Fake exception');

        $this->setNonAdminUser();
        $this->createAnyGoal();
    }

    public function testUpdateGoalShouldThrowExceptionIfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasWriteAccess Fake exception');

        $idGoal = $this->createAnyGoal();
        $this->assertSame(1, $idGoal); // make sure goal is created and does not already fail here
        $this->setNonAdminUser();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'url', 'www.test.de', 'exact');
    }

    public function testUpdateGoalShouldThrowExceptionIfPatternTypeIsExactAndMatchAttributeNotEvent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Goals_ExceptionInvalidMatchingString');

        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'url', 'www.test.de', 'exact');
    }

    public function testUpdateGoalShouldThrowExceptionIfPatternIsEmptyForExternalWebsite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_PleaseSpecifyValue');

        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'external_website', '', 'contains');
    }

    public function testUpdateGoalShouldNotThrowExceptionIfPatternTypeIsExactAndMatchAttributeIsEvent()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'event_action', 'www.test.de', 'exact');
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'event_category', 'www.test.de', 'exact');
        $this->api->updateGoal($this->idSite, $idGoal, 'MyName', 'event_name', 'www.test.de', 'exact');

        $this->assertSame(1, $idGoal);
    }

    /**
     * @dataProvider getNonGoalIdGoals
     */
    public function testUpdateGoalShouldThrowIfTheSiteHasNoSuchGoal($idGoal)
    {
        $this->createAnyGoal();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There is no goal with id');

        $this->api->updateGoal($this->idSite, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains');
    }

    public function testUpdateGoalShouldThrowIfTheGoalBelongsToAnotherSite()
    {
        $idGoal = $this->createAnyGoal();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There is no goal with id');

        $this->api->updateGoal($this->idSiteTwo, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains');
    }

    public function testUpdateGoalShouldThrowIfTheGoalWasDeleted()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->deleteGoal($this->idSite, $idGoal);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There is no goal with id');

        $this->api->updateGoal($this->idSite, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains');
    }

    public function testUpdateGoalShouldUpdateAllGivenFields()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains', true, 999, true);

        $this->assertGoal($idGoal, 'UpdatedName', '', 'file', 'http://www.updatetest.de', 'contains', 1, 999, 1);
    }

    public function testUpdateGoalShouldUpdateMinimalFieldsShouldLeaveOtherFieldsUntouched()
    {
        $idGoal = $this->createAnyGoal();
        $this->api->updateGoal($this->idSite, $idGoal, 'UpdatedName', 'file', 'http://www.updatetest.de', 'contains');

        $this->assertGoal($idGoal, 'UpdatedName', '', 'file', 'http://www.updatetest.de', 'contains', 0, 0, 0);
    }

    public function testDeleteGoalShouldNotDeleteAGoalIfGoalIdDoesNotExist()
    {
        $this->assertHasNoGoals();

        $this->createAnyGoal();
        $this->assertHasGoals();

        $this->api->deleteGoal($this->idSite, 999);
        $this->assertHasGoals();
    }

    public function testDeleteGoalShouldNotDeleteAGoalIfSiteDoesNotMatchGoalId()
    {
        $this->assertHasNoGoals();

        $idGoal = $this->createAnyGoal();
        $this->assertHasGoals();

        $this->api->deleteGoal($idSite = 2, $idGoal);
        $this->assertHasGoals();
    }

    public function testDeleteGoalShouldDeleteAGoalIfGoalAndSiteMatches()
    {
        $this->assertHasNoGoals();

        $idGoal = $this->createAnyGoal();
        $this->assertHasGoals();

        $this->api->deleteGoal($this->idSite, $idGoal);
        $this->assertHasNoGoals();
    }

    public function testDeleteGoalShouldDeleteTheConversionsOfThatGoal()
    {
        $idGoal = $this->createAnyGoal();
        $this->trackedConversion($this->idSite, $idGoal);
        $this->trackedConversion($this->idSiteTwo, $idGoal);

        $this->api->deleteGoal($this->idSite, $idGoal);

        $this->assertSame(0, $this->getConversionCount($this->idSite, $idGoal));
        $this->assertSame(1, $this->getConversionCount($this->idSiteTwo, $idGoal));
    }

    /**
     * @dataProvider getNonGoalIdGoals
     */
    public function testDeleteGoalShouldNotDeleteConversionsOfIdGoalsThatAreNoGoal($idGoal)
    {
        $this->createAnyGoal();
        $this->trackedConversion($this->idSite, GoalManager::IDGOAL_ORDER);
        $this->trackedConversion($this->idSite, GoalManager::IDGOAL_CART);

        $this->api->deleteGoal($this->idSite, $idGoal);

        $this->assertHasGoals();
        $this->assertSame(1, $this->getConversionCount($this->idSite, GoalManager::IDGOAL_ORDER));
        $this->assertSame(1, $this->getConversionCount($this->idSite, GoalManager::IDGOAL_CART));
    }

    public function getNonGoalIdGoals(): iterable
    {
        yield 'ecommerce order' => [GoalManager::IDGOAL_ORDER];
        yield 'abandoned cart' => [GoalManager::IDGOAL_CART];
        yield 'ecommerce order as string' => ['0'];
        yield 'abandoned cart as string' => ['-1'];
        yield 'ecommerce order label' => [Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER];
        yield 'abandoned cart label' => [Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART];
        yield 'unknown goal' => [999];
    }

    public function testGetGoalShouldThrowExceptionIfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('heckUserHasViewAccess Fake exception');

        $idGoal = $this->createAnyGoal();
        $this->assertSame(1, $idGoal);
        $this->setNonAdminUser();
        $this->api->getGoal($this->idSite, $idGoal);
    }

    public function testGetGoalShouldReturnNullIfItDoesNotExist()
    {
        $this->assertNull($this->api->getGoal($this->idSite, $idGoal = 99));
    }

    public function testGetGoalShouldReturnExistingGoal()
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

    /**
     * @param string|array $idSite
     *
     * @dataProvider getTestDataForMultipleSites
     */
    public function testGetGoalsShouldReturnGoalsForMultipleSites($idSite): void
    {
        $idGoal = $this->api->addGoal($this->idSite, 'Goal Site One', 'url', 'http://site.one', 'exact');
        $idGoalTwo = $this->api->addGoal($this->idSiteTwo, 'Goal Site Two', 'url', 'http://site.two', 'exact');
        $goals = $this->api->getGoals($idSite);

        $this->assertEqualsCanonicalizing(
            [
                $this->api->getGoal($this->idSite, $idGoal),
                $this->api->getGoal($this->idSiteTwo, $idGoalTwo),
            ],
            $goals
        );
    }

    public function getTestDataForMultipleSites(): iterable
    {
        yield 'comma separated string' => ['1,2'];
        yield 'array' => [[1, 2]];
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

    private function trackedConversion(int $idSite, int $idGoal): void
    {
        $this->idVisit++;

        Db::query(
            'INSERT INTO ' . Common::prefixTable('log_conversion')
            . ' (idvisit, idsite, idvisitor, server_time, idgoal, buster, url) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $this->idVisit,
                $idSite,
                Common::hex2bin('0123456789abcdef'),
                '2014-01-01 00:00:00',
                $idGoal,
                0,
                'http://example.org',
            ]
        );
    }

    private function getConversionCount(int $idSite, int $idGoal): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_conversion') . ' WHERE idsite = ? AND idgoal = ?',
            [$idSite, $idGoal]
        );
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
            'Piwik\Access' => new FakeAccess(),
        );
    }
}

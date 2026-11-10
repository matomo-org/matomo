<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\Live\MeasurableSettings as LiveMeasurableSettings;
use Piwik\Plugins\Live\SystemSettings as LiveSystemSettings;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * On a website whose compliance policy disables the visitor log, the GDPR tools only find, export and delete the
 * visits of visitors who gave consent.
 *
 * @group PrivacyManager
 * @group DataSubjectsConsentTest
 */
class DataSubjectsConsentTest extends IntegrationTestCase
{
    private const CONSENT_ERROR = 'Only visits from visitors who gave consent can be exported or deleted on this website.';

    /**
     * Site under the CNIL policy.
     */
    private int $policySite;

    /**
     * Site without any policy.
     */
    private int $otherSite;

    /**
     * Visit ids keyed by "<site>:<user>:<consent>".
     *
     * @var array<string, int>
     */
    private array $visits = [];

    private Date $time;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;
        Fixture::createSuperUser();
        Fixture::loadAllTranslations();

        $this->policySite = Fixture::createWebsite('2020-01-01 00:00:00');
        $this->otherSite = Fixture::createWebsite('2020-01-01 00:00:00');
        $this->time = Date::factory('2024-03-01 10:00:00');

        foreach (['policy' => $this->policySite, 'other' => $this->otherSite] as $siteKey => $idSite) {
            foreach (['alice', 'bob'] as $userId) {
                foreach (['1' => 1, '0' => 0, 'none' => null] as $consentKey => $consent) {
                    $this->visits["$siteKey:$userId:$consentKey"] = $this->trackVisit($idSite, $userId, $consent);
                }
            }
        }

        // the visits are tracked first, as the policy discards the user id while tracking
        CnilPolicy::setActiveStatus($this->policySite, true);
    }

    public function tearDown(): void
    {
        FakeAccess::$superUser = true;
        CnilPolicy::setActiveStatus(null, false);
        CnilPolicy::setActiveStatus($this->policySite, false);
        Fixture::resetTranslations();

        parent::tearDown();
    }

    /**
     * @dataProvider getSearchesOfThePolicySite
     *
     * @param string[] $expectedVisits
     */
    public function testFindDataSubjectsOnlyFindsConsentedVisitsOnThePolicySite(string $segment, array $expectedVisits): void
    {
        $this->assertSame($this->getVisitIds($expectedVisits), $this->findVisitIds($this->policySite, $segment));
    }

    /**
     * @return iterable<string, array{0: string, 1: string[]}>
     */
    public function getSearchesOfThePolicySite(): iterable
    {
        yield 'user id' => ['userId==alice', ['policy:alice:1']];
        yield 'or segment' => ['userId==alice,userId==bob', ['policy:alice:1', 'policy:bob:1']];
        yield 'segment already holding the consent condition in an or branch' => [
            'userId==alice;consentState==consented,userId==bob',
            ['policy:alice:1'],
        ];
        yield 'or segment containing the consent condition' => [
            'actions>=1;consentState==consented,userId==alice',
            ['policy:alice:1', 'policy:bob:1'],
        ];
        yield 'visitor id' => ['visitorId==' . substr(sha1('alice'), 0, 16), ['policy:alice:1']];
        yield 'segment asking for refused visits' => ['consentState==refused', []];
        yield 'segment asking for visits without consent' => ['consentState!=consented;userId==alice', []];
        yield 'segment ending in a backslash' => ['userId==alice\\', []];
    }

    public function testFindDataSubjectsFindsEveryMatchingVisitOnASiteWithoutPolicy(): void
    {
        $this->assertSame(
            $this->getVisitIds(['other:alice:1', 'other:alice:0', 'other:alice:none']),
            $this->findVisitIds($this->otherSite, 'userId==alice')
        );
    }

    /**
     * @dataProvider getSiteLists
     */
    public function testFindDataSubjectsCombinesThePolicySiteAndOtherSites(string $idSites): void
    {
        $idSites = str_replace(['P', 'O'], [(string) $this->policySite, (string) $this->otherSite], $idSites);

        $this->assertSame(
            $this->getVisitIds(['policy:alice:1', 'other:alice:1', 'other:alice:0', 'other:alice:none']),
            $this->findVisitIds($idSites, 'userId==alice')
        );
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public function getSiteLists(): iterable
    {
        yield 'list' => ['P,O'];
        yield 'all websites' => ['all'];
    }

    public function testFindDataSubjectsReturnsTheConsentOfEachVisit(): void
    {
        $result = $this->findDataSubjects($this->policySite . ',' . $this->otherSite, 'userId==alice');

        $consentByVisit = [];
        foreach ($result->getRows() as $row) {
            $consentByVisit[(int) $row->getColumn('idVisit')] = $row->getColumn('consent');
        }
        ksort($consentByVisit);

        $this->assertSame([
            $this->visits['policy:alice:1'] => 1,
            $this->visits['other:alice:1'] => 1,
            $this->visits['other:alice:0'] => 0,
            $this->visits['other:alice:none'] => '',
        ], $consentByVisit);
    }

    public function testFindDataSubjectsSkipsThePolicySiteWhenItsVisitorProfileIsDisabledInItsSettings(): void
    {
        $settings = new LiveMeasurableSettings($this->policySite);
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();

        $this->assertSame([], $this->findVisitIds($this->policySite, 'userId==alice'));
    }

    public function testFindDataSubjectsSkipsThePolicySiteWhenTheVisitorProfileIsDisabledInTheSystemSettings(): void
    {
        $settings = new LiveSystemSettings();
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();

        $this->assertSame([], $this->findVisitIds($this->policySite, 'userId==alice'));
    }

    public function testFindDataSubjectsSkipsASiteWithoutPolicyWhoseVisitorLogIsDisabledInItsSettings(): void
    {
        $settings = new LiveMeasurableSettings($this->otherSite);
        $settings->disableVisitorLog->setValue(true);
        $settings->save();

        $this->assertSame([], $this->findVisitIds($this->otherSite, 'userId==alice'));
    }

    public function testFindDataSubjectsSkipsThePolicySiteWhenItsVisitorLogIsAlsoDisabledInItsSettings(): void
    {
        $settings = new LiveMeasurableSettings($this->policySite);
        $settings->disableVisitorLog->setValue(true);
        $settings->save();

        $this->assertSame([], $this->findVisitIds($this->policySite, 'userId==alice'));
    }

    public function testFindDataSubjectsSkipsThePolicySiteForAUserWithOnlyViewAccessToIt(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = [$this->otherSite];
        FakeAccess::$idSitesView = [$this->policySite, $this->otherSite];

        $this->assertSame(
            $this->getVisitIds(['other:alice:1', 'other:alice:0', 'other:alice:none']),
            $this->findVisitIds($this->policySite . ',' . $this->otherSite, 'userId==alice')
        );
        $this->assertSame(
            ['available' => false, 'consentedVisitsOnly' => false, 'consentedVisitsOnlyIdSites' => []],
            $this->processRootRequest('PrivacyManager.getDataSubjectSearchScope', ['idSite' => $this->policySite])
        );
    }

    public function testFindDataSubjectsCountsOnlyConsentedVisitsTowardsTheLimitOnThePolicySite(): void
    {
        // more recent visits of alice without consent than the search returns
        $this->copyVisit($this->visits['policy:alice:0'], 401);

        $this->assertSame(
            $this->getVisitIds(['policy:alice:1']),
            $this->findVisitIds($this->policySite, 'userId==alice')
        );
    }

    public function testFindDataSubjectsKeepsTheNewest401VisitsAcrossThePolicySiteAndOtherSites(): void
    {
        // the policy site's consented visit is the newest of all, the site without policy holds 402 newer visits
        // than its own tracked ones
        $this->copyVisit($this->visits['other:alice:0'], 402);
        $newest = $this->copyVisit($this->visits['policy:alice:1'], 1, 1000);

        $result = $this->findDataSubjects($this->policySite . ',' . $this->otherSite, 'userId==alice');
        $idVisits = array_map('intval', $result->getColumn('idVisit'));

        $this->assertCount(401, $idVisits);
        $this->assertSame($newest[0], $idVisits[0], 'the newest visit comes first');
        $this->assertNotContains($this->visits['policy:alice:1'], $idVisits, 'the oldest visits are left out');

        $lastActionTimes = array_map(function ($idVisit) {
            return Db::fetchOne(
                'SELECT visit_last_action_time FROM ' . Common::prefixTable('log_visit') . ' WHERE idvisit = ?',
                [$idVisit]
            );
        }, $idVisits);
        $sorted = $lastActionTimes;
        rsort($sorted);
        $this->assertSame($sorted, $lastActionTimes, 'visits are sorted by last action, newest first');
    }

    public function testFindDataSubjectsFindsAConsentedVisitTrackedWhileThePolicyIsEnforced(): void
    {
        // without consent-aware analytics measured, the policy discards the user id, so the visit is found by its id
        $idVisit = $this->trackVisit($this->policySite, 'carol', 1);
        $idVisitWithoutConsent = $this->trackVisit($this->policySite, 'carol', null);

        $this->assertSame([$idVisit], $this->findVisitIds($this->policySite, 'visitId==' . $idVisit));
        $this->assertSame([], $this->findVisitIds($this->policySite, 'visitId==' . $idVisitWithoutConsent));
    }

    public function testFindDataSubjectsOnlyFindsConsentedVisitsWhenThePolicyIsEnforcedForEveryWebsite(): void
    {
        CnilPolicy::setActiveStatus($this->policySite, false);
        CnilPolicy::setActiveStatus(null, true);

        $this->assertSame(
            $this->getVisitIds(['policy:alice:1', 'other:alice:1']),
            $this->findVisitIds('all', 'userId==alice')
        );
    }

    public function testGetConsentOfVisitsReadsMoreVisitsThanOneQueryHolds(): void
    {
        $model = StaticContainer::get(DataSubjects::class);
        $idVisits = array_merge(range(100000, 101100), [$this->visits['other:alice:0'], $this->visits['policy:alice:1']]);

        $this->assertSame([
            $this->visits['policy:alice:1'] => ['idsite' => $this->policySite, 'consent' => 1],
            $this->visits['other:alice:0'] => ['idsite' => $this->otherSite, 'consent' => 0],
        ], $this->sortByKey($model->getConsentOfVisits($idVisits)));
    }

    /**
     * @dataProvider getDataSubjectMethods
     */
    public function testConsentedVisitOfThePolicySiteCanBeExportedAndDeleted(string $method): void
    {
        $this->callDataSubjectMethod($method, [$this->visit('policy:alice:1')]);

        $this->assertVisitExists('policy:alice:1', $method !== 'deleteDataSubjects');
    }

    /**
     * @dataProvider getRefusedVisits
     *
     * @param array<int, array{idsite: int|string, idvisit: int|string}|string> $visits
     */
    public function testVisitsOfThePolicySiteWithoutConsentCannotBeExportedOrDeleted(string $method, array $visits): void
    {
        $visits = array_map(function ($visit) {
            return is_string($visit) ? $this->visit($visit) : $this->resolveVisit($visit);
        }, $visits);

        try {
            $this->callDataSubjectMethod($method, $visits);
            $this->fail('Expected the visits to be refused');
        } catch (\Exception $e) {
            $this->assertStringStartsWith(self::CONSENT_ERROR, $e->getMessage());
        }

        // nothing is deleted, not even the consented visit of a refused request
        foreach (['policy:alice:1', 'policy:alice:0', 'policy:alice:none', 'other:alice:0'] as $visit) {
            $this->assertVisitExists($visit, true);
        }
    }

    /**
     * @return iterable<string, array{0: string, 1: array<int, mixed>}>
     */
    public function getRefusedVisits(): iterable
    {
        foreach ($this->getDataSubjectMethods() as [$method]) {
            yield "$method refused consent" => [$method, ['policy:alice:0']];
            yield "$method no consent" => [$method, ['policy:alice:none']];
            yield "$method consented and refused" => [$method, ['policy:alice:1', 'policy:alice:0']];
            yield "$method unknown visit" => [$method, [['idsite' => 'P', 'idvisit' => 999999]]];
            yield "$method consented visit of another site" => [$method, [['idsite' => 'P', 'idvisit' => 'other:alice:1']]];
            yield "$method visit ids given as strings" => [$method, [['idsite' => 'P', 'idvisit' => 'policy:alice:0']]];
        }
    }

    /**
     * @dataProvider getDataSubjectMethods
     */
    public function testVisitsWithoutConsentOfASiteWithoutPolicyCanStillBeExportedAndDeleted(string $method): void
    {
        $this->callDataSubjectMethod($method, [$this->visit('other:alice:0'), $this->visit('other:alice:none')]);

        $this->assertVisitExists('other:alice:0', $method !== 'deleteDataSubjects');
        $this->assertVisitExists('other:alice:none', $method !== 'deleteDataSubjects');
    }

    /**
     * @dataProvider getInvalidVisitIds
     *
     * @param mixed $idSite
     * @param mixed $idVisit
     */
    public function testInvalidVisitIdsAreRefused(string $method, $idSite, $idVisit, string $expectedError): void
    {
        $this->expectExceptionMessage($expectedError);

        $this->callDataSubjectMethod($method, [[
            'idsite' => $idSite === 'P' ? $this->policySite : $idSite,
            'idvisit' => $idVisit,
        ]]);
    }

    /**
     * @return iterable<string, array{0: string, 1: mixed, 2: mixed, 3: string}>
     */
    public function getInvalidVisitIds(): iterable
    {
        foreach ($this->getDataSubjectMethods() as [$method]) {
            yield "$method empty idvisit" => [$method, 'P', '', 'No idvisit key set'];
            yield "$method negative idvisit" => [$method, 'P', -1, 'Invalid idvisit'];
            yield "$method sql in idvisit" => [$method, 'P', '1 OR 1', 'Invalid idvisit'];
            yield "$method digits followed by text" => [$method, 'P', '3abc', 'Invalid idvisit'];
            yield "$method array idvisit" => [$method, 'P', [1], 'Invalid idvisit'];
            yield "$method sql in idsite" => [$method, '1 OR 1', 1, 'Invalid idsite'];
        }
    }

    /**
     * @dataProvider getDataSubjectMethods
     */
    public function testUserWithoutAdminAccessIsRefusedBeforeConsentIsChecked(string $method): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = [$this->otherSite];
        FakeAccess::$idSitesView = [$this->policySite, $this->otherSite];

        // the fake access object reports a missing access this way
        $this->expectExceptionMessage('checkUserHasAdminAccess');

        $this->callDataSubjectMethod($method, [$this->visit('policy:alice:0')]);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public function getDataSubjectMethods(): iterable
    {
        yield 'export' => ['exportDataSubjects'];
        yield 'delete' => ['deleteDataSubjects'];
    }

    /**
     * @dataProvider getSearchScopes
     *
     * @param array{available: bool, consentedVisitsOnly: bool, consentedVisitsOnlyIdSites: string[]} $expected
     */
    public function testGetDataSubjectSearchScope(string $idSites, bool $disablePolicySiteProfile, array $expected): void
    {
        if ($disablePolicySiteProfile) {
            $settings = new LiveMeasurableSettings($this->policySite);
            $settings->disableVisitorProfile->setValue(true);
            $settings->save();
        }

        $idSites = str_replace(['P', 'O'], [(string) $this->policySite, (string) $this->otherSite], $idSites);
        $expected['consentedVisitsOnlyIdSites'] = array_map(function ($site) {
            return $site === 'P' ? $this->policySite : $this->otherSite;
        }, $expected['consentedVisitsOnlyIdSites']);

        $this->assertSame($expected, $this->processRootRequest('PrivacyManager.getDataSubjectSearchScope', [
            'idSite' => $idSites,
        ]));
    }

    /**
     * @return iterable<string, array{0: string, 1: bool, 2: array{available: bool, consentedVisitsOnly: bool, consentedVisitsOnlyIdSites: string[]}}>
     */
    public function getSearchScopes(): iterable
    {
        yield 'policy site' => ['P', false, ['available' => true, 'consentedVisitsOnly' => true, 'consentedVisitsOnlyIdSites' => ['P']]];
        yield 'site without policy' => ['O', false, ['available' => true, 'consentedVisitsOnly' => false, 'consentedVisitsOnlyIdSites' => []]];
        yield 'both sites' => ['P,O', false, ['available' => true, 'consentedVisitsOnly' => true, 'consentedVisitsOnlyIdSites' => ['P']]];
        yield 'policy site with profile disabled' => ['P', true, ['available' => false, 'consentedVisitsOnly' => false, 'consentedVisitsOnlyIdSites' => []]];
        yield 'both sites with policy site profile disabled' => ['P,O', true, ['available' => false, 'consentedVisitsOnly' => false, 'consentedVisitsOnlyIdSites' => []]];
    }

    /**
     * @dataProvider getSegmentListings
     */
    public function testIdentifyingSegmentsAreOnlyListedForTheGdprToolsOnThePolicySite(
        string $site,
        bool $forDataSubjectSearch,
        bool $isSuperUser,
        bool $expectedListed
    ): void {
        if (!$isSuperUser) {
            FakeAccess::$superUser = false;
            FakeAccess::$idSitesAdmin = [];
            FakeAccess::$idSitesView = [$this->policySite, $this->otherSite];
        }

        $idSite = str_replace(['P', 'O'], [(string) $this->policySite, (string) $this->otherSite], $site);
        $parameters = ['idSites' => $idSite, 'idSite' => $idSite, 'filter_limit' => -1];
        if ($forDataSubjectSearch) {
            $parameters['forDataSubjectSearch'] = 1;
        }

        $this->setRequestParameters($parameters);

        try {
            $segments = array_column($this->processRootRequest('API.getSegmentsMetadata', $parameters), 'segment');
        } finally {
            $this->setRequestParameters([]);
        }

        foreach (['userId', 'visitorId', 'visitId'] as $segment) {
            $this->assertSame($expectedListed, in_array($segment, $segments, true), $segment);
        }
    }

    /**
     * @return iterable<string, array{0: string, 1: bool, 2: bool, 3: bool}>
     */
    public function getSegmentListings(): iterable
    {
        yield 'policy site, gdpr tools' => ['P', true, true, true];
        yield 'policy site, elsewhere' => ['P', false, true, false];
        yield 'policy site, gdpr tools, user without admin access' => ['P', true, false, false];
        yield 'site without policy, elsewhere' => ['O', false, true, true];
        yield 'both sites, gdpr tools' => ['P,O', true, true, true];
        yield 'all websites, gdpr tools' => ['all', true, true, true];
    }

    public function testIdentifyingSegmentsAreNotListedForTheGdprToolsWhenThePolicySiteProfileIsDisabled(): void
    {
        $settings = new LiveMeasurableSettings($this->policySite);
        $settings->disableVisitorProfile->setValue(true);
        $settings->save();

        $parameters = [
            'idSites' => $this->policySite,
            'idSite' => $this->policySite,
            'filter_limit' => -1,
            'forDataSubjectSearch' => 1,
        ];
        $this->setRequestParameters($parameters);

        try {
            $segments = array_column($this->processRootRequest('API.getSegmentsMetadata', $parameters), 'segment');
        } finally {
            $this->setRequestParameters([]);
        }

        $this->assertNotContains('userId', $segments);
        $this->assertNotContains('visitorId', $segments);
    }

    /**
     * The request being served, which the segment list reads its websites and parameters from.
     *
     * @param array<string, mixed> $parameters
     */
    private function setRequestParameters(array $parameters): void
    {
        $_GET = $parameters;
        \Piwik\Cache::getTransientCache()->flushAll();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    private function trackVisit(int $idSite, string $userId, ?int $consent): int
    {
        $this->time = $this->time->addHour(1);

        $tracker = Fixture::getTracker($idSite, $this->time->getDatetime(), true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUserId($userId);
        $tracker->setForceNewVisit();
        Fixture::checkResponse($tracker->doTrackPageView('page'));

        $idVisit = (int) Db::fetchOne('SELECT MAX(idvisit) FROM ' . Common::prefixTable('log_visit'));

        // the consent is written directly, as tracking only records it on a site measuring consent-aware analytics
        Db::query(
            'UPDATE ' . Common::prefixTable('log_visit') . ' SET consent = ? WHERE idvisit = ?',
            [$consent, $idVisit]
        );

        return $idVisit;
    }

    /**
     * @return int[] the ids of the copies
     */
    private function copyVisit(int $idVisit, int $count, int $daysLater = 0): array
    {
        $table = Common::prefixTable('log_visit');
        $row = Db::fetchRow('SELECT * FROM ' . $table . ' WHERE idvisit = ?', [$idVisit]);
        unset($row['idvisit']);

        $lastActionTime = Date::factory($row['visit_last_action_time']);
        $columns = array_keys($row);
        $idVisits = [];

        for ($i = 1; $i <= $count; $i++) {
            $time = $lastActionTime->addDay($daysLater + $i)->getDatetime();
            $row['visit_first_action_time'] = $row['visit_last_action_time'] = $time;

            Db::query(
                'INSERT INTO ' . $table . ' (`' . implode('`, `', $columns) . '`) VALUES (' . Common::getSqlStringFieldsArray($row) . ')',
                array_values($row)
            );
            $idVisits[] = (int) Db::get()->lastInsertId();
        }

        return $idVisits;
    }

    /**
     * @param array<int, mixed> $array
     * @return array<int, mixed>
     */
    private function sortByKey(array $array): array
    {
        ksort($array);

        return $array;
    }

    private function findDataSubjects(string $idSite, string $segment): DataTable
    {
        $result = $this->processRootRequest('PrivacyManager.findDataSubjects', [
            'idSite' => $idSite,
            'segment' => $segment,
        ]);

        // no website could be searched
        return $result instanceof DataTable ? $result : new DataTable();
    }

    /**
     * Processes the API request as the request being served, as Live checks which request that is.
     *
     * @param array<string, mixed> $parameters
     * @return mixed
     */
    private function processRootRequest(string $method, array $parameters)
    {
        Request::setIsRootRequestApiRequest($method);

        try {
            return Request::processRequest($method, $parameters);
        } finally {
            Request::setIsRootRequestApiRequest(null);
        }
    }

    /**
     * @param int|string $idSite
     * @return int[]
     */
    private function findVisitIds($idSite, string $segment): array
    {
        $result = $this->findDataSubjects((string) $idSite, $segment);

        $idVisits = array_map('intval', $result->getColumn('idVisit'));
        sort($idVisits);

        return $idVisits;
    }

    /**
     * @param string[] $keys
     * @return int[]
     */
    private function getVisitIds(array $keys): array
    {
        $idVisits = array_map(function ($key) {
            return $this->visits[$key];
        }, $keys);
        sort($idVisits);

        return $idVisits;
    }

    /**
     * @return array{idsite: int, idvisit: int}
     */
    private function visit(string $key): array
    {
        return [
            'idsite' => str_starts_with($key, 'policy:') ? $this->policySite : $this->otherSite,
            'idvisit' => $this->visits[$key],
        ];
    }

    /**
     * @param array{idsite: int|string, idvisit: int|string} $visit
     * @return array{idsite: int|string, idvisit: int|string}
     */
    private function resolveVisit(array $visit): array
    {
        if ($visit['idsite'] === 'P') {
            $visit['idsite'] = $this->policySite;
        }

        if (is_string($visit['idvisit']) && isset($this->visits[$visit['idvisit']])) {
            // given as a string, as the visits of a request arrive
            $visit['idvisit'] = (string) $this->visits[$visit['idvisit']];
            $visit['idsite'] = (string) $visit['idsite'];
        }

        return $visit;
    }

    /**
     * @param array<int, array<string, mixed>> $visits
     */
    private function callDataSubjectMethod(string $method, array $visits): void
    {
        $this->processRootRequest('PrivacyManager.' . $method, ['visits' => $visits]);
    }

    private function assertVisitExists(string $key, bool $exists): void
    {
        $count = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idvisit = ?',
            [$this->visits[$key]]
        );

        $this->assertSame($exists ? 1 : 0, $count, $key);
    }
}

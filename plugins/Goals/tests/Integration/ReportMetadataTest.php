<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\API\ProcessedReport;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Goals
 * @group Plugins
 * @group ReportMetadataTest
 */
class ReportMetadataTest extends IntegrationTestCase
{
    private const DATE = '2015-01-02';

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var int
     */
    private $idGoal;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser(true);
        $this->idSite = Fixture::createWebsite('2015-01-01 00:00:00', $ecommerce = 1);
        $this->idGoal = GoalsApi::getInstance()->addGoal(
            $this->idSite,
            'Thank you page',
            'url',
            'thank-you',
            'contains'
        );

        $tracker = Fixture::getTracker($this->idSite, self::DATE . ' 10:00:00');
        $tracker->setUrl('http://piwik.net/thank-you');
        Fixture::checkResponse($tracker->doTrackPageView('Thank you'));

        $orderTracker = Fixture::getTracker($this->idSite, self::DATE . ' 11:00:00');
        $orderTracker->setUrl('http://piwik.net/checkout');
        Fixture::checkResponse($orderTracker->doTrackPageView('Checkout'));
        Fixture::checkResponse($orderTracker->doTrackEcommerceOrder('order-one', 100, 90, 10));
    }

    public function testTheRetiredGoalOverviewReportsAreNoLongerAdvertised(): void
    {
        $uniqueIds = $this->getReportUniqueIds();

        self::assertNotContains('Goals_get_idGoal--0', $uniqueIds);
        self::assertNotContains('Goals_getVisitsUntilConversion_idGoal--0', $uniqueIds);
        self::assertNotContains('Goals_getDaysToConversion_idGoal--0', $uniqueIds);
    }

    public function testTheAllGoalsAndEcommerceOrderReportsAreStillAdvertised(): void
    {
        $uniqueIds = $this->getReportUniqueIds();

        self::assertContains('Goals_get', $uniqueIds);
        self::assertContains('Goals_getVisitsUntilConversion', $uniqueIds);
        self::assertContains('Goals_getDaysToConversion', $uniqueIds);
        self::assertContains('Goals_get_idGoal--ecommerceOrder', $uniqueIds);
        self::assertContains('Goals_get_idGoal--' . $this->idGoal, $uniqueIds);
    }

    /**
     * @dataProvider getRetiredUniqueIds
     */
    public function testARetiredUniqueIdResolvesToTheReportHoldingItsFiguresOnAnEcommerceSite(
        string $retiredUniqueId,
        string $expectedUniqueId
    ): void {
        $report = $this->getProcessedReport()->getReportMetadataByUniqueId($this->idSite, $retiredUniqueId);

        self::assertIsArray($report);
        self::assertSame($expectedUniqueId, $report['uniqueId']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function getRetiredUniqueIds(): iterable
    {
        yield 'goals overview' => ['Goals_get_idGoal--0', 'Goals_get_idGoal--ecommerceOrder'];
        yield 'visits until conversion overview' => [
            'Goals_getVisitsUntilConversion_idGoal--0',
            'Goals_getVisitsUntilConversion',
        ];
        yield 'days to conversion overview' => [
            'Goals_getDaysToConversion_idGoal--0',
            'Goals_getDaysToConversion',
        ];
    }

    /**
     * @dataProvider getRetiredUniqueIdFallbacks
     */
    public function testARetiredUniqueIdFallsBackToTheAllGoalsReportWithoutEcommerce(
        string $retiredUniqueId,
        string $expectedUniqueId
    ): void {
        $idSiteWithoutEcommerce = Fixture::createWebsite('2015-01-01 00:00:00', $ecommerce = 0);
        GoalsApi::getInstance()->addGoal(
            $idSiteWithoutEcommerce,
            'Thank you page',
            'url',
            'thank-you',
            'contains'
        );

        $report = $this->getProcessedReport()
            ->getReportMetadataByUniqueId($idSiteWithoutEcommerce, $retiredUniqueId);

        self::assertIsArray($report);
        self::assertSame($expectedUniqueId, $report['uniqueId']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function getRetiredUniqueIdFallbacks(): iterable
    {
        yield 'goals overview' => ['Goals_get_idGoal--0', 'Goals_get'];
        yield 'visits until conversion overview' => [
            'Goals_getVisitsUntilConversion_idGoal--0',
            'Goals_getVisitsUntilConversion',
        ];
        yield 'days to conversion overview' => [
            'Goals_getDaysToConversion_idGoal--0',
            'Goals_getDaysToConversion',
        ];
    }

    public function testTheNumericEcommerceOrderGoalIdStillResolvesThroughReportMetadata(): void
    {
        $report = $this->getProcessedReport()->getMetadata($this->idSite, 'Goals', 'get', ['idGoal' => 0]);

        self::assertIsArray($report);
        self::assertSame('Goals_get_idGoal--ecommerceOrder', reset($report)['uniqueId']);
    }

    /**
     * The numeric id only names the ecommerce order report, so on a site without it no report is
     * found rather than the all-goals report, whose figures the request's data would not hold.
     */
    public function testTheNumericEcommerceOrderGoalIdFindsNoReportWithoutEcommerce(): void
    {
        $idSiteWithoutEcommerce = $this->createSiteWithoutEcommerce();

        self::assertIsArray($this->getProcessedReport()->getMetadata($idSiteWithoutEcommerce, 'Goals', 'get'));
        self::assertFalse(
            $this->getProcessedReport()->getMetadata($idSiteWithoutEcommerce, 'Goals', 'get', ['idGoal' => 0])
        );
    }

    /**
     * The conversion distributions read `idGoal=0` as all goals, so addressing them numerically
     * resolves to the all-goals report whether or not the site has ecommerce.
     *
     * @dataProvider getConversionDistributionReports
     */
    public function testTheNumericEcommerceOrderGoalIdResolvesTheConversionDistributionsToAllGoals(
        string $apiAction,
        bool $ecommerce
    ): void {
        $idSite = $ecommerce ? $this->idSite : $this->createSiteWithoutEcommerce();

        $report = $this->getProcessedReport()->getMetadata($idSite, 'Goals', $apiAction, ['idGoal' => 0]);

        self::assertIsArray($report);
        self::assertSame('Goals_' . $apiAction, reset($report)['uniqueId']);
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public function getConversionDistributionReports(): iterable
    {
        yield 'visits until conversion with ecommerce' => ['getVisitsUntilConversion', true];
        yield 'visits until conversion without ecommerce' => ['getVisitsUntilConversion', false];
        yield 'days to conversion with ecommerce' => ['getDaysToConversion', true];
        yield 'days to conversion without ecommerce' => ['getDaysToConversion', false];
    }

    /**
     * @dataProvider getConversionDistributionMethods
     */
    public function testTheConversionDistributionsReadTheNumericEcommerceOrderGoalIdAsAllGoals(string $apiMethod): void
    {
        // An order on a second visit sets the ecommerce order distribution apart from the all-goals one.
        $returningTracker = Fixture::getTracker($this->idSite, self::DATE . ' 08:00:00');
        $returningTracker->setVisitorId('a1b2c3d4e5f60718');
        $returningTracker->setUrl('http://piwik.net/home');
        Fixture::checkResponse($returningTracker->doTrackPageView('Home'));
        $returningTracker->setForceVisitDateTime(self::DATE . ' 12:00:00');
        $returningTracker->setForceNewVisit();
        $returningTracker->setUrl('http://piwik.net/checkout');
        Fixture::checkResponse($returningTracker->doTrackEcommerceOrder('order-two', 50, 45, 5));

        $params = [
            'idSite' => $this->idSite,
            'period' => 'day',
            'date' => self::DATE,
        ];

        $allGoals = Request::processRequest($apiMethod, $params);
        $ecommerceOrder = Request::processRequest($apiMethod, $params + ['idGoal' => 'ecommerceOrder']);
        $numericZero = Request::processRequest($apiMethod, $params + ['idGoal' => 0]);

        self::assertNotSame($ecommerceOrder->getColumn('nb_conversions'), $allGoals->getColumn('nb_conversions'));
        self::assertSame($allGoals->getColumn('label'), $numericZero->getColumn('label'));
        self::assertSame($allGoals->getColumn('nb_conversions'), $numericZero->getColumn('nb_conversions'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getConversionDistributionMethods(): iterable
    {
        yield 'visits until conversion' => ['Goals.getVisitsUntilConversion'];
        yield 'days to conversion' => ['Goals.getDaysToConversion'];
    }

    /**
     * @dataProvider getConversionDistributionMethods
     */
    public function testTheRowEvolutionOfAConversionDistributionReadsTheNumericEcommerceOrderGoalIdAsAllGoals(
        string $apiMethod
    ): void {
        [$apiModule, $apiAction] = explode('.', $apiMethod);

        // range labels are only distinct once translated, and the row evolution is looked up by label
        Fixture::loadAllTranslations();

        try {
            $distribution = Request::processRequest($apiMethod, [
                'idSite' => $this->idSite,
                'period' => 'day',
                'date' => self::DATE,
            ]);
            $convertedRow = $distribution->getRowFromId(0);
            self::assertGreaterThan(0, $convertedRow->getColumn('nb_conversions'));

            $params = [
                'idSite' => $this->idSite,
                'period' => 'day',
                'date' => '2015-01-01,2015-01-03',
                'apiModule' => $apiModule,
                'apiAction' => $apiAction,
                'label' => $convertedRow->getColumn('label'),
                'format' => 'json',
            ];

            $allGoals = Request::processRequest('API.getRowEvolution', $params);
            $numericZero = Request::processRequest('API.getRowEvolution', $params + ['idGoal' => 0]);

            self::assertStringContainsString('"nb_conversions":' . $convertedRow->getColumn('nb_conversions'), $allGoals);
            self::assertSame($allGoals, $numericZero);
        } finally {
            Fixture::resetTranslations();
        }
    }

    /**
     * @dataProvider getNonCanonicalZeroGoalIds
     */
    public function testANonCanonicalZeroGoalIdFindsNoReport(string $idGoal): void
    {
        foreach (['get', 'getVisitsUntilConversion', 'getDaysToConversion'] as $apiAction) {
            self::assertFalse(
                $this->getProcessedReport()->getMetadata($this->idSite, 'Goals', $apiAction, ['idGoal' => $idGoal]),
                "Goals.$apiAction with idGoal=$idGoal"
            );
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getNonCanonicalZeroGoalIds(): iterable
    {
        yield 'decimal' => ['0.9'];
        yield 'zero with decimals' => ['0.0'];
        yield 'negative zero' => ['-0'];
        yield 'exponent' => ['0e3'];
        yield 'leading space' => [' 0'];
    }

    /**
     * @return list<string>
     */
    private function getReportUniqueIds(): array
    {
        $metadata = Request::processRequest('API.getReportMetadata', [
            'idSite' => $this->idSite,
            'filter_limit' => '-1',
        ]);

        return array_column($metadata, 'uniqueId');
    }

    /**
     * @param int|false $idGoal
     */
    private function getConversions($idGoal = false): int
    {
        $params = [
            'idSite' => $this->idSite,
            'period' => 'day',
            'date' => self::DATE,
        ];

        if (false !== $idGoal) {
            $params['idGoal'] = $idGoal;
        }

        $table = Request::processRequest('Goals.get', $params);

        return (int) $table->getFirstRow()->getColumn('nb_conversions');
    }

    private function createSiteWithoutEcommerce(): int
    {
        $idSite = Fixture::createWebsite('2015-01-01 00:00:00', $ecommerce = 0);
        GoalsApi::getInstance()->addGoal($idSite, 'Thank you page', 'url', 'thank-you', 'contains');

        return (int) $idSite;
    }

    private function getProcessedReport(): ProcessedReport
    {
        return StaticContainer::get(ProcessedReport::class);
    }
}

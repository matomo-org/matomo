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
    public function testARetiredUniqueIdStillResolvesToTheAllGoalsReport(
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

    public function testTheAllGoalsConversionsAreTheSumAcrossGoalsAndNotTheEcommerceOrderFigure(): void
    {
        $allGoals = $this->getConversions();
        $ecommerceOrder = $this->getConversions(0);
        $goal = $this->getConversions($this->idGoal);

        self::assertSame(1, $ecommerceOrder);
        self::assertSame(1, $goal);
        self::assertSame($ecommerceOrder + $goal, $allGoals);
        self::assertNotSame($ecommerceOrder, $allGoals);
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

    private function getProcessedReport(): ProcessedReport
    {
        return StaticContainer::get(ProcessedReport::class);
    }
}

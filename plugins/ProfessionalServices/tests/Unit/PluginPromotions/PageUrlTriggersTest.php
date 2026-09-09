<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\FormPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\WooCommerceUrlsTrigger;

/**
 * The promotions that read the page URLs report.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class PageUrlTriggersTest extends TestCase
{
    private FormPageTrigger $formPages;

    private WooCommerceUrlsTrigger $cartUrls;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formPages = new FormPageTrigger(
            $this->createMock(ArchivedReportReader::class),
            $this->createMock(ReportPeriod::class),
            $this->createMock(DailyTriggerCache::class)
        );
        $this->cartUrls = new WooCommerceUrlsTrigger(
            $this->createMock(ArchivedReportReader::class),
            $this->createMock(ReportPeriod::class),
            $this->createMock(DailyTriggerCache::class)
        );
    }

    /**
     * @dataProvider getFormUrlCases
     */
    public function testEveryConventionalFormPathIsRecognised(string $label, bool $expectedToQualify): void
    {
        $report = $this->makeReport([['label' => $label, 'nb_visits' => 800, 'nb_hits' => 900]]);

        $this->assertSame($expectedToQualify, null !== $this->formPages->findQualifyingFormPage($report));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function getFormUrlCases(): array
    {
        return [
            '/contact-us' => ['/contact-us', true],
            '/checkout' => ['/checkout', true],
            '/signup' => ['/signup', true],
            '/login' => ['/login', true],
            'nested under a locale' => ['/en/contact-us/', true],
            'a step within checkout' => ['/checkout/step-2', true],
            'matched case insensitively' => ['/Login', true],
            'an ordinary page' => ['/pricing', false],
            'the home page' => ['/', false],
        ];
    }

    /**
     * @dataProvider getFormVisitCases
     */
    public function testTheFormPageAppliesItsVisitFloor(int $visits, bool $expectedToQualify): void
    {
        $report = $this->makeReport([['label' => '/checkout', 'nb_visits' => $visits, 'nb_hits' => $visits]]);

        $this->assertSame($expectedToQualify, null !== $this->formPages->findQualifyingFormPage($report));
    }

    /**
     * @return array<string, array{int, bool}>
     */
    public function getFormVisitCases(): array
    {
        return [
            'just below the floor' => [499, false],
            'at the floor' => [500, true],
            'well above the floor' => [5000, true],
        ];
    }

    public function testTheBusiestFormPageWins(): void
    {
        // Ordered by visits descending, the way the report is requested.
        $report = $this->makeReport([
            ['label' => '/pricing', 'nb_visits' => 4000, 'nb_hits' => 4200],
            ['label' => '/checkout', 'nb_visits' => 900, 'nb_hits' => 1100],
            ['label' => '/login', 'nb_visits' => 700, 'nb_hits' => 800],
        ]);

        $page = $this->formPages->findQualifyingFormPage($report);

        // /pricing is busier but carries no form, so the busiest page that does wins.
        $this->assertSame('/checkout', $page['url']);
        $this->assertSame(900, $page['count']);
    }

    public function testCartPageviewsAreAddedUpAcrossEveryProduct(): void
    {
        // A shop has one cart URL per product, so no single one need be busy.
        $report = $this->makeReport([
            ['label' => '/shop/?add-to-cart=11', 'nb_visits' => 40, 'nb_hits' => 60],
            ['label' => '/shop/?add-to-cart=22', 'nb_visits' => 30, 'nb_hits' => 55],
            ['label' => '/pricing', 'nb_visits' => 900, 'nb_hits' => 2000],
        ]);

        $this->assertSame(115, $this->cartUrls->countCartPageviews($report));
    }

    /**
     * Whether a query string survives into the label depends on the website's URL
     * settings, and the parameter only ever exists in one.
     */
    public function testACartUrlIsRecognisedFromTheUrlMetadataAlone(): void
    {
        $report = new DataTable();
        $row = new Row([Row::COLUMNS => ['label' => '/shop', 'nb_visits' => 80, 'nb_hits' => 120]]);
        $row->setMetadata('url', 'http://example.org/shop?add-to-cart=99');
        $report->addRow($row);

        $this->assertSame(120, $this->cartUrls->countCartPageviews($report));
    }

    public function testAShopWithoutCartActivityCountsNothing(): void
    {
        $report = $this->makeReport([['label' => '/shop', 'nb_visits' => 900, 'nb_hits' => 2000]]);

        $this->assertSame(0, $this->cartUrls->countCartPageviews($report));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function makeReport(array $rows): DataTable
    {
        $report = new DataTable();

        foreach ($rows as $columns) {
            $report->addRow(new Row([Row::COLUMNS => $columns]));
        }

        return $report;
    }
}

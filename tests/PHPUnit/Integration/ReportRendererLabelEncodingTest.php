<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\DataTable;
use Piwik\DataTable\Simple;
use Piwik\ReportRenderer\Html;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Row labels reach the HTML report body encoded, whether they come from the data table or are
 * built from metric names. Both are covered here, because a mismatch either way is silent.
 *
 * @group Core
 * @group ReportRenderer
 */
class ReportRendererLabelEncodingTest extends IntegrationTestCase
{
    /**
     * Metric names become row labels, so they are encoded like labels.
     */
    public function testMetricNamesUsedAsRowLabelsAreEncoded()
    {
        $reportData = new Simple();
        $reportData->addRowFromSimpleArray(['nb_visits' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Visits Summary', 'uniqueId' => 'VisitsSummary_get'],
            'reportData' => $reportData,
            'columns' => ['nb_visits' => 'Custom <metric> & "name"'],
        ]);

        self::assertStringContainsString('Custom &lt;metric&gt; &amp; &quot;name&quot;', $rendered);
        self::assertStringNotContainsString('Custom <metric>', $rendered);
    }

    /**
     * Metric names do not all arrive in the same shape, so the encoding has to be idempotent and
     * has to leave + and %XX alone - which is why this is sanitizeInputValues(), not decodeLabelSafe().
     */
    public function testMetricNamesAreNotDoubleEncodedOrDecoded()
    {
        $reportData = new Simple();
        $reportData->addRowFromSimpleArray(['nb_visits' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Visits Summary', 'uniqueId' => 'VisitsSummary_get'],
            'reportData' => $reportData,
            'columns' => ['nb_visits' => 'Custom &amp; Metric + 50%2F'],
        ]);

        self::assertStringContainsString('Custom &amp; Metric + 50%2F', $rendered);
        self::assertStringNotContainsString('&amp;amp;', $rendered);
    }

    /**
     * A line break inside a metric name has to reach the template, where the HTML collapses it to
     * a space. sanitizeInputValue() would delete it instead and run the words either side together.
     */
    public function testLineBreaksInMetricNamesAreNotStripped()
    {
        $reportData = new Simple();
        $reportData->addRowFromSimpleArray(['nb_visits' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Visits Summary', 'uniqueId' => 'VisitsSummary_get'],
            'reportData' => $reportData,
            'columns' => ['nb_visits' => "Custom metric\nname"],
        ]);

        self::assertStringContainsString("metric\nname", $rendered);
        self::assertStringNotContainsString('metricname', $rendered);
    }

    /**
     * Labels from the data table are already encoded, so the renderer must not encode them again.
     */
    public function testDataTableLabelsAreNotDoubleEncoded()
    {
        $reportData = new DataTable();
        $reportData->addRowFromSimpleArray(['label' => 'Electronics &amp; Cameras', 'nb_visits' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Products', 'uniqueId' => 'Ecommerce_getItemCategory', 'dimension' => 'Product'],
            'reportData' => $reportData,
            'columns' => ['label' => 'Product', 'nb_visits' => 'Visits'],
        ]);

        self::assertStringContainsString('Electronics &amp; Cameras', $rendered);
        self::assertStringNotContainsString('&amp;amp;', $rendered);
    }

    /**
     * Column headers are escaped by Twig, so encoding them here as well would show the entities.
     */
    public function testColumnHeadersAreNotDoubleEncoded()
    {
        $reportData = new DataTable();
        $reportData->addRowFromSimpleArray(['label' => 'Downloads', 'nb_visits' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Pages', 'uniqueId' => 'Actions_getPageUrls', 'dimension' => 'Page URL'],
            'reportData' => $reportData,
            'columns' => ['label' => 'Page URL', 'nb_visits' => 'Visits & Views'],
        ]);

        self::assertStringContainsString('Visits &amp; Views', $rendered);
        self::assertStringNotContainsString('&amp;amp;', $rendered);
    }

    private function renderReport(array $processedReport): string
    {
        $renderer = new Html();
        $renderer->renderReport($processedReport + [
            'reportMetadata' => new DataTable(),
            'displayTable' => true,
            'displayGraph' => false,
            'evolutionGraph' => false,
            'segment' => null,
        ]);

        return $renderer->getRenderedReport();
    }
}

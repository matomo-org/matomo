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
 * Row labels reach the HTML report body in one representation: encoded. Labels taken from the data
 * table are encoded by the SafeDecodeLabel filter, and the ones built from metric names for reports
 * without a dimension are encoded by the renderer.
 *
 * Both sides are covered here, because a mismatch either way is silent.
 *
 * @group Core
 * @group ReportRenderer
 */
class ReportRendererLabelEncodingTest extends IntegrationTestCase
{
    /**
     * Metric names are free text and can contain markup characters, so they need encoding before
     * they are used as labels.
     */
    public function testMetricNamesUsedAsRowLabelsAreEncoded()
    {
        $reportData = new Simple();
        $reportData->addRowFromSimpleArray(['nb_conversions' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Goals', 'uniqueId' => 'Goals_get'],
            'reportData' => $reportData,
            'columns' => ['nb_conversions' => 'Conversions goal "<b>Sale</b> & Co" (ID 1 )'],
        ]);

        self::assertStringNotContainsString('<b>', $rendered);
        self::assertStringContainsString('Conversions goal &quot;&lt;b&gt;Sale&lt;/b&gt; &amp; Co&quot; (ID 1 )', $rendered);
    }

    /**
     * Metric names do not all arrive in the same shape: some are plain text, others are already
     * encoded by the time they get here. Encoding has to be idempotent so the second kind is not
     * shown with its entities, and it has to leave + and %XX alone, which is why this uses
     * Common::sanitizeInputValue() rather than the label filter's decodeLabelSafe().
     */
    public function testMetricNamesAreNotDoubleEncodedOrDecoded()
    {
        $reportData = new Simple();
        $reportData->addRowFromSimpleArray(['nb_conversions' => 5]);

        $rendered = $this->renderReport([
            'metadata' => ['name' => 'Goals', 'uniqueId' => 'Goals_get'],
            'reportData' => $reportData,
            'columns' => ['nb_conversions' => 'Custom &amp; Metric + 50%2F'],
        ]);

        self::assertStringContainsString('Custom &amp; Metric + 50%2F', $rendered);
        self::assertStringNotContainsString('&amp;amp;', $rendered);
    }

    /**
     * Labels that come from the data table are encoded before they reach the template, so they are
     * printed as they are. Encoding them a second time would show the entities to the reader.
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

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\ReportRenderer;

use Piwik\DataTable;
use Piwik\ReportRenderer\Tsv;
use PHPUnit\Framework\TestCase;

/**
 * @group Core
 */
class TsvTest extends TestCase
{
    private function buildProcessedReport(string $name): array
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ['label' => 'row', 'nb_conversions' => 1],
        ]);

        return [
            'reportData' => $table,
            'metadata'   => [
                'uniqueId' => 'Goals_get',
                'name'     => $name,
            ],
        ];
    }

    /**
     * The report-name line must be neutralized the same way the CSV renderer
     * neutralizes it, so a name carrying control characters or a leading
     * formula character cannot split the output into a new, executable record.
     */
    public function testRenderReportNeutralizesReportNameLineBreaksAndFormulas()
    {
        $renderer = new Tsv();
        $renderer->renderReport($this->buildProcessedReport("Goal A\r\n=FORMULA_MARKER"));

        $rendered = $renderer->getRenderedReport();
        $firstLine = strtok($rendered, "\n");

        // The whole report name is wrapped in quotes, so the embedded line
        // break and the leading formula character stay inside a single field
        // instead of forming a new, executable record. (A vulnerable renderer
        // would emit a bare "Goal A" line followed by an unquoted "=..." record.)
        self::assertSame('"Goal A ', $firstLine);
        self::assertStringContainsString('"Goal A ' . "\n" . '=FORMULA_MARKER"', $rendered);

        // The raw CR is replaced rather than carried through to the output.
        self::assertStringNotContainsString("\r", $rendered);
    }

    public function testRenderReportLeavesPlainReportNameUnquoted()
    {
        $renderer = new Tsv();
        $renderer->renderReport($this->buildProcessedReport('Visits Summary'));

        $rendered = $renderer->getRenderedReport();

        self::assertSame('Visits Summary', strtok($rendered, "\n"));
    }
}

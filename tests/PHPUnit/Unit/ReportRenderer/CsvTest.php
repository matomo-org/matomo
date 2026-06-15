<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\ReportRenderer;

use Piwik\DataTable;
use Piwik\DataTable\Renderer\Csv as CsvDataTableRenderer;
use Piwik\ReportRenderer\Csv;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @group ReportRendererTest
 * @group Csv
 */
class CsvTest extends TestCase
{
    /**
     * @var Csv
     */
    private $csvReportRenderer;

    public function setUp(): void
    {
        parent::setUp();
        $this->csvReportRenderer = new Csv();
    }

    public function testGetRendererSetsTranslateColumnNamesToTrue()
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ['label' => 'test', 'nb_visits' => 5],
        ]);

        $method = new ReflectionMethod(Csv::class, 'getRenderer');
        $method->setAccessible(true);

        /** @var CsvDataTableRenderer $renderer */
        $renderer = $method->invoke($this->csvReportRenderer, $table, 'TestPlugin_testMethod');

        $this->assertInstanceOf(CsvDataTableRenderer::class, $renderer);
        $this->assertTrue($renderer->translateColumnNames, 'translateColumnNames should be set to true');
    }
}

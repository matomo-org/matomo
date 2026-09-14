<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\DataTable\Row;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\View;

/**
 * The ratio tooltip is not the tooltip the browser shows for a title attribute: the data table
 * renders that attribute as HTML (see handleCellTooltips() in dataTable.js), and the browser has
 * decoded it once by the time it is read back - so a row label encoded only once is markup again.
 *
 * @group CoreVisualizations
 * @group RatioTooltipEncodingTest
 * @group Plugins
 */
class RatioTooltipEncodingTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();

        parent::tearDown();
    }

    /**
     * Row labels come from the tracker, so they reach the template HTML encoded once.
     */
    public function testMarkupInARowLabelStaysTextInTheTooltip(): void
    {
        $title = $this->renderTooltipTitle('&lt;b style=color:red&gt;label&lt;/b&gt;');

        $readBack = $this->decodeAttribute($title);

        self::assertStringNotContainsString('<b ', $readBack);
        self::assertStringContainsString('&lt;b style=color:red&gt;label&lt;/b&gt;', $readBack);
    }

    /**
     * Encoding the tooltip twice would be just as wrong as encoding it once: the entities would
     * then be what the user reads.
     */
    public function testTheTooltipStillReadsAsPlainTextForAnOrdinaryLabel(): void
    {
        $title = $this->renderTooltipTitle('Ben &amp; Jerry&#039;s');

        $displayed = $this->decodeAttribute($this->decodeAttribute($title));

        self::assertStringContainsString('\'Ben & Jerry\'s\' represents 12.5% of 1234 Visits', $displayed);
        self::assertStringContainsString('in segment "All visits"', $displayed);
    }

    /**
     * Comparison rows append a second sentence, separated by the line breaks the template writes
     * into the attribute itself. Those have to stay markup while the sentence around them does not.
     */
    public function testTheSeparatorStaysMarkupWhileTheAppendedSentenceDoesNot(): void
    {
        $title = $this->renderTooltipTitle('Keyword', '2.5% more than &lt;b&gt;All visits&lt;/b&gt;');

        self::assertStringContainsString('<br/><br/>', $title);
        self::assertStringNotContainsString('<b>', $this->decodeAttribute($title));
    }

    /**
     * The value of the title attribute as the tooltip reads it, ie. after the HTML parser has
     * decoded the attribute once.
     */
    private function decodeAttribute(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function renderTooltipTitle(string $label, string $tooltipSuffix = ''): string
    {
        $view = new View('@CoreVisualizations/_dataTableViz_htmlTable_ratio');
        $view->sendHeadersWhenRendering = false;
        $view->column = 'nb_visits';
        $view->row = new Row([Row::COLUMNS => ['label' => $label, 'nb_visits' => 154]]);
        $view->totals = ['nb_visits' => 1234];
        $view->properties = ['report_ratio_columns' => ['nb_visits']];
        $view->label = $label;
        $view->labelColumn = 'label';
        $view->translations = ['nb_visits' => 'Visits', 'label' => 'Keyword'];
        $view->rowPercentage = '12.5%';
        $view->segmentTitlePretty = 'All visits';
        $view->tooltipSuffix = $tooltipSuffix;

        $matched = preg_match('/<span class="ratio"\s+title="([^"]*)"/', $view->render(), $matches);

        self::assertSame(1, $matched, 'the ratio span was not rendered');

        return $matches[1];
    }
}

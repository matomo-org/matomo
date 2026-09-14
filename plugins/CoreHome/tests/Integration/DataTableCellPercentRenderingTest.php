<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translation\Translator;
use Piwik\View;

/**
 * A metric reaches the cell templates already formatted for the display language, so in locales
 * whose percent pattern leads with the sign - tr and ku, and eu behind a non-breaking space - it
 * arrives as "%15" rather than "15%". Decoding such a value as a URL reads the sign and the digits
 * behind it as an escape sequence, which silently rewrites the number the report shows.
 *
 * @group CoreHome
 * @group DataTableCellPercentRenderingTest
 * @group Plugins
 */
class DataTableCellPercentRenderingTest extends IntegrationTestCase
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
     * @dataProvider getLeadingPercentSignValues
     */
    public function testAPercentValueSurvivesTheCellTemplateWhereTheSignLeads(
        string $language,
        string $formattedValue
    ): void {
        self::assertSame($formattedValue, $this->renderCellValue($language, $formattedValue));
    }

    public function getLeadingPercentSignValues(): array
    {
        return [
            // "%37" is a valid percent escape for "7", so the leading digit used to be eaten
            ['tr', '%37,63'],
            ['ku', '%37,63'],
            // "%10" decodes to a C0 control character, which browsers draw as a missing glyph
            ['tr', '%100'],
            // "%85" decodes to a byte that is not valid UTF-8, and was dropped altogether -
            // leaving a cell that read as no data rather than as a broken one
            ['tr', '%85'],
            ['tr', '%0'],
        ];
    }

    /**
     * The trailing-sign convention every other locale uses has to keep rendering as it did.
     */
    public function testAPercentValueIsUnchangedWhereTheSignTrails(): void
    {
        self::assertSame('37.63%', $this->renderCellValue('en', '37.63%'));
        self::assertSame('100%', $this->renderCellValue('en', '100%'));
    }

    /**
     * The value cell is not decoded any more, so escaping is all that stands between a column
     * value and the page.
     */
    public function testMarkupInAValueColumnIsNotRenderedAsMarkup(): void
    {
        $rendered = $this->renderCellValue('en', '<b style=color:red>12</b>');

        self::assertStringNotContainsString('<b ', $rendered);
        self::assertStringContainsString('&lt;b style=color:red&gt;12&lt;/b&gt;', $rendered);
    }

    /**
     * Flattening a report with its dimensions shown gives each dimension a column of its own, and
     * those hold tracked labels - already encoded once - rather than metrics. They are the one
     * kind of value in this branch that still has to be read as a URL.
     */
    public function testADimensionColumnOfAFlattenedTableIsDecodedLikeALabel(): void
    {
        $rendered = $this->renderCellValue('en', 'Tom &amp; Jerry / search%20results', ['bounce_rate']);

        self::assertSame('Tom &amp; Jerry / search results', $rendered);
    }

    /**
     * Piwik\Metrics\Formatter\Html writes the entity rather than the character, and
     * Piwik\Plugin\Visualization gives every visualization that formatter, so pretty time, size
     * and money values all arrive here carrying it. Rendering the entity as text instead of
     * resolving it widens the column and shifts the whole table.
     *
     * @dataProvider getValuesCarryingEntities
     */
    public function testAnEntityWrittenByTheFormatterIsResolvedRatherThanShown(
        string $formattedValue,
        string $expected
    ): void {
        self::assertSame($expected, $this->renderCellValue('en', $formattedValue));
    }

    public function getValuesCarryingEntities(): array
    {
        return [
            // Formatter\Html::replaceSpaceWithNonBreakingSpace()
            ['3&nbsp;min&nbsp;21s', "3\u{a0}min\u{a0}21s"],
            ['128&nbsp;M', "128\u{a0}M"],
            // a value already encoded once stays encoded exactly once
            ['Tom &amp; Jerry', 'Tom &amp; Jerry'],
        ];
    }

    private function renderCellValue(string $language, string $formattedValue, array $dimensions = []): string
    {
        $this->setLanguage($language);

        $dataTable = new DataTable();
        $dataTable->setMetadata('dimensions', $dimensions);

        $view = new View('@CoreHome/_dataTableCell');
        $view->sendHeadersWhenRendering = false;
        $view->column = 'bounce_rate';
        $view->row = new Row([Row::COLUMNS => ['label' => 'Page', 'bounce_rate' => $formattedValue]]);
        $view->dataTable = $dataTable;
        $view->columns_to_display = ['label', 'bounce_rate'];
        $view->properties = [
            'translations' => ['bounce_rate' => 'Bounce Rate'],
            // no ratio column, so the hover markup the cell includes stays out of the way
            'report_ratio_columns' => [],
            // read by the label markup, which a dimension column goes through too
            'tooltip_metadata_name' => '',
        ];

        $matched = preg_match('#<span class="value">(.*?)</span>#s', $view->render(), $matches);
        self::assertSame(1, $matched, 'the value span was not rendered');

        $rendered = trim($matches[1]);
        self::assertNoControlCharacters($rendered);

        return $rendered;
    }

    private function setLanguage(string $language): void
    {
        StaticContainer::get(Translator::class)->setCurrentLanguage($language);
    }

    private static function assertNoControlCharacters(string $rendered): void
    {
        self::assertSame(
            0,
            preg_match('/[\x00-\x1F]/', $rendered),
            'the rendered value contains a control character: ' . bin2hex($rendered)
        );
    }
}

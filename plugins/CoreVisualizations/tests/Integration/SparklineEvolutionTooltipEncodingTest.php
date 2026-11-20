<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translation\Translator;
use Piwik\Twig;

/**
 * Every evolution tooltip on a sparkline quotes two formatted metric values and the evolution
 * between them, so in a locale whose percent pattern leads with the sign it carries "%12,5" three
 * times over. Reading that as a URL takes the sign and the digits behind it for an escape.
 *
 * @group CoreVisualizations
 * @group SparklineEvolutionTooltipEncodingTest
 * @group Plugins
 */
class SparklineEvolutionTooltipEncodingTest extends IntegrationTestCase
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
     * A rate metric reaches the tooltip formatted, and so does the evolution figure beside it,
     * whatever the metric - so in these locales an ordinary visits sparkline carried the sign too.
     *
     * @dataProvider getLeadingPercentSignTooltips
     */
    public function testALeadingPercentSignSurvivesInTheTooltip(
        string $language,
        string $currentValue,
        string $pastValue,
        string $evolution
    ): void {
        $this->setLanguage($language);

        $readBack = $this->decodeAttribute($this->renderTooltipTitle(
            $this->evolutionSummary($currentValue, $pastValue, $evolution),
            $evolution
        ));

        self::assertStringContainsString($currentValue, $readBack);
        self::assertStringContainsString($pastValue, $readBack);
        self::assertStringContainsString($evolution, $readBack);
        self::assertStringNotContainsString("\u{fffd}", $readBack);
    }

    public function getLeadingPercentSignTooltips(): array
    {
        return [
            // "%37" is a valid percent escape for "7", so the leading digit used to be eaten
            ['tr', '%37,63', '%33,44', '%12,5'],
            ['ku', '%37,63', '%33,44', '%12,5'],
            // "%10" decodes to a C0 control character and "%85" to a byte that is not valid UTF-8
            ['tr', '%100', '%85', '%17,6'],
        ];
    }

    /**
     * The trailing-sign convention every other locale uses has to keep rendering as it did.
     */
    public function testAPercentageIsUnchangedWhereTheSignTrails(): void
    {
        $this->setLanguage('en');

        $readBack = $this->decodeAttribute($this->renderTooltipTitle(
            $this->evolutionSummary('37.63%', '33.44%', '12.5%')
        ));

        self::assertStringContainsString('37.63% Bounce Rate in September 2026', $readBack);
        self::assertStringContainsString('Evolution: 12.5%', $readBack);
    }

    /**
     * The tooltip is no longer decoded, so escaping is all that stands between it and the page.
     */
    public function testMarkupInATooltipStaysText(): void
    {
        $this->setLanguage('en');

        $title = $this->renderTooltipTitle($this->evolutionSummary(
            '<b style=color:red>12</b>',
            '10',
            '20%'
        ));

        self::assertStringNotContainsString('<b ', $this->decodeAttribute($title));
    }

    private function evolutionSummary(string $currentValue, string $pastValue, string $evolution): string
    {
        return Piwik::translate('General_EvolutionSummaryGeneric', [
            $currentValue . ' Bounce Rate',
            'September 2026',
            $pastValue . ' Bounce Rate',
            'August 2026',
            $evolution,
        ]);
    }

    private function renderTooltipTitle(string $tooltip, string $percent = '12.5%'): string
    {
        $template = StaticContainer::get(Twig::class)->getTwigEnvironment()->createTemplate(
            '{% import "@CoreVisualizations/macros.twig" as macros %}{{ macros.sparklineEvolution(evolution) }}'
        );

        $rendered = $template->render(['evolution' => [
            'percent' => $percent,
            'trend' => 4.19,
            'isLowerValueBetter' => false,
            'tooltip' => $tooltip,
        ]]);

        $matched = preg_match('/<span class="metricEvolution" title="([^"]*)"/', $rendered, $matches);
        self::assertSame(1, $matched, 'the evolution span was not rendered');

        return $matches[1];
    }

    /**
     * The value of the title attribute as the tooltip reads it, ie. after the HTML parser has
     * decoded the attribute once.
     */
    private function decodeAttribute(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function setLanguage(string $language): void
    {
        StaticContainer::get(Translator::class)->setCurrentLanguage($language);
    }
}

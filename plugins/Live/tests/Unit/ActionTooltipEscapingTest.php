<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Live\Live;

/**
 * @group Live
 * @group ActionTooltipEscapingTest
 * @group Plugins
 */
class ActionTooltipEscapingTest extends TestCase
{
    /**
     * The tooltip reads the title back after the attribute parse and renders it as HTML, so an
     * entry has to arrive with two layers for its markup to be displayed rather than rendered.
     */
    public function testAnEntryIsEscapedTwiceSoItsMarkupIsDisplayed()
    {
        $escaped = Live::escapeActionTooltipEntry('&lt;b&gt;bold&lt;/b&gt;');

        $this->assertSame('&amp;lt;b&amp;gt;bold&amp;lt;/b&amp;gt;', $escaped);
        $this->assertSame('<b>bold</b>', $this->asDisplayed($escaped));
    }

    public function testAnEntryThatArrivesUnescapedEndsAtTheSameDepth()
    {
        $this->assertSame(
            Live::escapeActionTooltipEntry('&lt;b&gt;bold&lt;/b&gt;'),
            Live::escapeActionTooltipEntry('<b>bold</b>')
        );
    }

    /**
     * Every in-tree listener separates its fields with a bare newline, which the client turns into
     * a `<br>`. Escaping through `Common` would delete them and run the fields together.
     */
    public function testTheLineBreaksTheEntriesAreSeparatedBySurvive()
    {
        $escaped = Live::escapeActionTooltipEntry("09:15:03\nhttps://example.org/page\nTime on page: 12s");

        $this->assertSame(2, substr_count($escaped, "\n"));
        $this->assertSame(
            "09:15:03\nhttps://example.org/page\nTime on page: 12s",
            $this->asDisplayed($escaped)
        );
    }

    public function testAnEmptyEntryStaysEmpty()
    {
        $this->assertSame('', Live::escapeActionTooltipEntry(null));
        $this->assertSame('', Live::escapeActionTooltipEntry(''));
    }

    /**
     * What a viewer ends up seeing: the browser decodes the attribute once, the tooltip renders the
     * result as HTML which decodes it a second time.
     */
    private function asDisplayed(string $attributeValue): string
    {
        return html_entity_decode(
            html_entity_decode($attributeValue, ENT_QUOTES, 'UTF-8'),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

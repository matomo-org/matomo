<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

/**
 * Shared by the tests covering the two tracking code generators, so that the values they are held
 * against stay the same for both.
 */
trait TrackingCodeTrait
{
    /**
     * Values that must never be able to change the markup of the page the tracking code is embedded in.
     */
    private function getUnsafeValueList(): array
    {
        return [
            'html tag'           => '<img src=x onerror=alert(1)>',
            'closing script'     => '</script><script>alert(1)</script>',
            'attribute breakout' => '" autofocus onfocus=alert(1) x="',
            'html entities'      => '&lt;img src=x onerror=alert(1)&gt;',
            'double quote'       => 'abc"def',
            'single quote'       => "abc'def",
            'backslash'          => 'abc\\"def',
            'angle brackets'     => '<>',
            'line break'         => "abc\ndef",
            'null byte'          => "abc\0def",
            'unicode'            => "äöü \u{2028}\u{1f600}",
        ];
    }

    public function getUnsafeValues(): iterable
    {
        foreach ($this->getUnsafeValueList() as $label => $value) {
            yield $label => [$value];
        }
    }

    /**
     * The tracking code is copied out of the rendered page, so this is what a site ends up embedding.
     */
    private function getRenderedTrackingCode(string $trackingCode): string
    {
        return html_entity_decode($trackingCode, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * The generated tracking code is written into the page unescaped, as element content and as a
     * double quoted attribute value, so it has to be escaped already. A single quote is left as it is,
     * as none of the places the code is written to is quoted with one.
     */
    private function assertTrackingCodeIsSafeToEmbed(string $trackingCode): void
    {
        self::assertStringNotContainsString('<', $trackingCode, 'The tracking code contains an unescaped "<"');
        self::assertStringNotContainsString('>', $trackingCode, 'The tracking code contains an unescaped ">"');
        self::assertStringNotContainsString('"', $trackingCode, 'The tracking code contains an unescaped double quote');
    }
}

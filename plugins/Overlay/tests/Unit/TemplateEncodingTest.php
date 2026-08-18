<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Overlay\tests\Unit;

/**
 * The Overlay handshake parameters (idSite, period, date, segment) are written into
 * inline JavaScript string literals. They must always be JS-encoded there, so a change
 * that drops the encoding is caught before it ships.
 */
class TemplateEncodingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getHandshakeTemplates
     */
    public function testHandshakeParametersAreJavascriptEncoded(string $templateFile, array $params)
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/templates/' . $templateFile);
        $this->assertNotFalse($contents, "Could not read template $templateFile");

        foreach ($params as $param) {
            $this->assertStringContainsString(
                "{{ $param|e('js') }}",
                $contents,
                "$templateFile must JS-encode the '$param' handshake parameter"
            );

            $this->assertStringNotContainsString(
                "{{ $param }}",
                $contents,
                "$templateFile must not write '$param' into JavaScript without JS-encoding"
            );
        }
    }

    public function getHandshakeTemplates(): array
    {
        return [
            ['index.twig', ['idSite', 'period', 'rawDate', 'segment']],
            ['index_noframe.twig', ['idSite', 'period', 'date', 'segment']],
        ];
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Plugins\DebugView\Model\DebugRequests;

/**
 * @group DebugView
 * @group DebugViewDebugRequestsTest
 * @group Plugins
 */
class DebugRequestsTest extends TestCase
{
    /**
     * @dataProvider getDecodeStoredParametersTestData
     */
    public function testDecodeStoredParameters(string $storedJson, ?array $expected)
    {
        $log = new DebugRequests(new RawRequestLog());

        $this->assertSame($expected, $log->decodeStoredParameters($storedJson));
    }

    public function getDecodeStoredParametersTestData(): array
    {
        return [
            'full format' => [
                '{"query":{"idsite":"1","debug":"1"},"defaults":{"userAgent":"curl"},'
                    . '"other":{"isAuthenticated":true},"actionType":94}',
                [
                    'query'      => ['idsite' => '1', 'debug' => '1'],
                    'defaults'   => ['userAgent' => 'curl'],
                    'other'      => ['isAuthenticated' => true],
                    'actionType' => 94,
                    'bot'        => null,
                ],
            ],
            'without defaults, other and action type' => [
                '{"query":{"idsite":"1"},"defaults":[],"other":[],"actionType":null}',
                [
                    'query'      => ['idsite' => '1'],
                    'defaults'   => null,
                    'other'      => null,
                    'actionType' => null,
                    'bot'        => null,
                ],
            ],
            'rows stored before the other group and action type existed' => [
                '{"query":{"idsite":"1"},"defaults":{"userAgent":"curl"}}',
                [
                    'query'      => ['idsite' => '1'],
                    'defaults'   => ['userAgent' => 'curl'],
                    'other'      => null,
                    'actionType' => null,
                    'bot'        => null,
                ],
            ],
            'bot request row' => [
                '{"query":{"idsite":"1","debug":"1"},"defaults":[],"other":[],'
                    . '"actionType":null,"bot":{"name":"Googlebot"}}',
                [
                    'query'      => ['idsite' => '1', 'debug' => '1'],
                    'defaults'   => null,
                    'other'      => null,
                    'actionType' => null,
                    'bot'        => ['name' => 'Googlebot'],
                ],
            ],
            'empty bot group normalises to null' => [
                '{"query":{"idsite":"1"},"bot":[]}',
                [
                    'query'      => ['idsite' => '1'],
                    'defaults'   => null,
                    'other'      => null,
                    'actionType' => null,
                    'bot'        => null,
                ],
            ],
            'flat map without query key is unusable' => [
                '{"idsite":"1","debug":"1"}',
                null,
            ],
            'invalid json' => ['{broken', null],
            'empty object' => ['{}', null],
            'scalar json'  => ['42', null],
        ];
    }

    public function testTruncateOversizedValuesShortensLongStringsWithMarker()
    {
        $log = new DebugRequests(new RawRequestLog());
        $long = str_repeat('a', DebugRequests::MAX_PARAM_VALUE_LENGTH + 500);

        $result = $log->truncateOversizedValues(['hsr' => $long, 'ok' => 'short']);

        $this->assertSame(
            str_repeat('a', DebugRequests::MAX_PARAM_VALUE_LENGTH) . DebugRequests::TRUNCATION_MARKER,
            $result['hsr']
        );
        $this->assertSame('short', $result['ok']);
    }

    public function testTruncateOversizedValuesKeepsValuesAtExactlyTheLimit()
    {
        $log = new DebugRequests(new RawRequestLog());
        $exact = str_repeat('b', DebugRequests::MAX_PARAM_VALUE_LENGTH);

        $result = $log->truncateOversizedValues(['v' => $exact]);

        $this->assertSame($exact, $result['v']);
    }

    public function testTruncateOversizedValuesIsMultibyteSafe()
    {
        $log = new DebugRequests(new RawRequestLog());
        $long = str_repeat('ä', DebugRequests::MAX_PARAM_VALUE_LENGTH + 10);

        $result = $log->truncateOversizedValues(['v' => $long]);

        $truncated = $result['v'];
        $this->assertSame(
            str_repeat('ä', DebugRequests::MAX_PARAM_VALUE_LENGTH) . DebugRequests::TRUNCATION_MARKER,
            $truncated
        );
        // the whole point: the truncated value must still be encodable
        $this->assertNotFalse(json_encode($truncated));
    }

    public function testTruncateOversizedValuesRecursesIntoNestedArrays()
    {
        $log = new DebugRequests(new RawRequestLog());
        $long = str_repeat('c', DebugRequests::MAX_PARAM_VALUE_LENGTH + 1);

        $result = $log->truncateOversizedValues([
            'clientHints' => ['fullVersionList' => ['long' => $long], 'model' => 'ok'],
            'number'      => 42,
            'bool'        => true,
        ]);

        $this->assertStringEndsWith(DebugRequests::TRUNCATION_MARKER, $result['clientHints']['fullVersionList']['long']);
        $this->assertSame('ok', $result['clientHints']['model']);
        $this->assertSame(42, $result['number']);
        $this->assertTrue($result['bool']);
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Framework;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestRequest\Response;

/**
 * API responses compare by value on SQLite, but names and identifiers compare as they are.
 *
 * @group Core
 */
class SqliteResponseNormalizationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('Normalization applies on SQLite only');
        }
    }

    /**
     * @dataProvider getResponses
     */
    public function testNormalizes(string $format, string $response, string $expected): void
    {
        $this->assertSame($expected, $this->normalize($format, $response));
    }

    public function getResponses(): iterable
    {
        yield 'JSON sums and decimals' => ['json', '{"nb_visits":"260","revenue":12.50,"list":["3",4.10]}', '{"nb_visits":260,"revenue":12.5,"list":[3,4.1]}'];
        yield 'JSON names and identifiers' => ['json', '{"label":"1.20","sku":"007","userId":"260","visitorId":"1234"}', '{"label":"1.20","sku":"007","userId":"260","visitorId":"1234"}'];
        yield 'JSON empty key' => ['json', '{"":"2"}', '{"":2}'];
        yield 'XML' => ['xml', '<row><label>1.20</label><revenue>12.50</revenue></row>', '<row><label>1.20</label><revenue>12.5</revenue></row>'];
        yield 'CSV label column' => ['csv', "label,revenue\n1.20,12.50\n", "label,revenue\n1.20,12.5\n"];
        yield 'CSV label after the date' => ['csv', "date,label,revenue\r\n2020-01-01,1.20,12.50\r\n", "date,label,revenue\r\n2020-01-01,1.20,12.5\r\n"];
        yield 'CSV single metric' => ['csv', "value\n12.50\n", "value\n12.5\n"];
        yield 'CSV with a byte-order mark' => ['csv', "\xEF\xBB\xBFlabel,revenue\n1.20,12.50", "\xEF\xBB\xBFlabel,revenue\n1.20,12.5"];
        yield 'TSV' => ['tsv', "label\tnb_visits\n1.20\t3.50", "label\tnb_visits\n1.20\t3.5"];
    }

    private function normalize(string $format, string $response): string
    {
        $object = new Response($response, [], ['format' => $format], false);
        return \Closure::bind(function (string $response) {
            return $this->normalizeNumbersOnSqlite($response);
        }, $object, Response::class)($response);
    }
}

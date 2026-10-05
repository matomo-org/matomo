<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\tests\Unit\ClickhouseBench;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\Engine;
use Piwik\Plugins\CoreConsole\ClickhouseBench\TidewaysSupport;

/**
 * @group CoreConsole
 * @group ClickhouseBench
 * @group Plugins
 */
class TidewaysSupportTest extends TestCase
{
    public function testWithoutASessionTheChildOnlyReportsMeasurements(): void
    {
        $env = TidewaysSupport::environment('bench', Engine::fromKey('clickhouse'));

        self::assertSame(['TIDEWAYS_SERVICE' => 'bench-clickhouse', 'TIDEWAYS_SAMPLERATE' => '100'], $env);
    }

    /**
     * A session without a correlation id would produce a trace no link points at, and an id
     * without a session a link with nothing behind it - so it is both or neither.
     */
    public function testTheSessionAndCorrelationIdAreOnlyPassedTogether(): void
    {
        $engine = Engine::fromKey('mysql');

        $env = TidewaysSupport::environment('bench', $engine, 'method=x', 'ref-1');
        self::assertSame('method=x', $env['TIDEWAYS_SESSION']);
        self::assertSame('ref-1', $env['TIDEWAYS_REF']);

        self::assertArrayNotHasKey('TIDEWAYS_SESSION', TidewaysSupport::environment('bench', $engine, 'method=x', null));
        self::assertArrayNotHasKey('TIDEWAYS_REF', TidewaysSupport::environment('bench', $engine, null, 'ref-1'));
    }

    public function testACorrelationIdIsAVersionFourUuid(): void
    {
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            TidewaysSupport::makeRef()
        );
        self::assertNotSame(TidewaysSupport::makeRef(), TidewaysSupport::makeRef());
    }

    public function testTheTraceUrlFiltersOnTheCorrelationId(): void
    {
        self::assertSame(
            'https://app.tideways.io/o/acme/bench/traces?cid=ref-1',
            TidewaysSupport::traceUrl('acme/bench', 'ref-1')
        );
        self::assertSame('', TidewaysSupport::traceUrl('', 'ref-1'));
        self::assertSame('', TidewaysSupport::traceUrl('acme/bench', ''));
    }
}

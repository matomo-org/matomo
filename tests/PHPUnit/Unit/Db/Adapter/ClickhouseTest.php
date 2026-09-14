<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Adapter;

use Piwik\Db\Adapter\Clickhouse;

/**
 * @group Core
 * @group ClickHouse
 */
class ClickhouseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * FINAL defaults to ON. While a CDC pipe is writing it is a correctness setting - an
     * UPDATE lands as a second row version - so an install that configures nothing has to get
     * the safe answer, not the fast one.
     */
    public function testFinalIsOnWhenNothingIsConfigured()
    {
        self::assertSame(1, Clickhouse::finalSetting([]));
        self::assertSame(1, Clickhouse::finalSetting(['final' => '']));
    }

    /**
     * @dataProvider getFinalSettingValues
     */
    public function testFinalIsOnlyTurnedOffByAnExplicitFalseyValue(int $expected, $configured)
    {
        self::assertSame($expected, Clickhouse::finalSetting(['final' => $configured]));
    }

    public function getFinalSettingValues(): array
    {
        return [
            'zero'        => [0, '0'],
            'false'       => [0, 'false'],
            'off'         => [0, 'off'],
            'no'          => [0, 'no'],
            'one'         => [1, '1'],
            'true'        => [1, 'true'],
            'on'          => [1, 'on'],
            // Anything filter_var cannot read as a boolean is not a request to turn it off.
            'nonsense'    => [1, 'maybe'],
        ];
    }

    public function testConvertPositionalBindsProducesFixedWidthNamedParams()
    {
        [$sql, $params] = Clickhouse::convertPositionalBinds(
            'SELECT * FROM log_visit WHERE idsite = ? AND visit_last_action_time >= ?',
            [1, '2026-08-20 00:00:00']
        );

        self::assertSame('SELECT * FROM log_visit WHERE idsite = :chBind000 AND visit_last_action_time >= :chBind001', $sql);
        self::assertSame(['chBind000' => 1, 'chBind001' => '2026-08-20 00:00:00'], $params);
    }

    public function testConvertPositionalBindsIgnoresQuestionMarksInsideStringLiterals()
    {
        [$sql, $params] = Clickhouse::convertPositionalBinds(
            "SELECT * FROM t WHERE url = 'http://example.org/?q=1' AND idsite = ?",
            [5]
        );

        self::assertSame("SELECT * FROM t WHERE url = 'http://example.org/?q=1' AND idsite = :chBind000", $sql);
        self::assertSame(['chBind000' => 5], $params);
    }

    public function testConvertPositionalBindsThrowsOnTooFewBindValues()
    {
        $this->expectExceptionMessage('more `?` placeholders than bind values');
        Clickhouse::convertPositionalBinds('SELECT ? , ?', [1]);
    }

    public function testConvertPositionalBindsThrowsOnTooManyBindValues()
    {
        $this->expectExceptionMessage('1 `?` placeholders for 2 bind values');
        Clickhouse::convertPositionalBinds('SELECT ?', [1, 2]);
    }

    public function testConvertPositionalBindsHexEncodesBinaryValues()
    {
        $idvisitor = hex2bin('001122334455ff99');

        [, $params] = Clickhouse::convertPositionalBinds('SELECT ? FROM t', [$idvisitor]);

        self::assertSame('001122334455ff99', $params['chBind000']);
    }

    public function testConvertPositionalBindsLeavesPrintableStringsAlone()
    {
        [, $params] = Clickhouse::convertPositionalBinds('SELECT ?, ?', ['abcd', 'abcdefgh']);

        self::assertSame('abcd', $params['chBind000']);
        self::assertSame('abcdefgh', $params['chBind001']);
    }
}

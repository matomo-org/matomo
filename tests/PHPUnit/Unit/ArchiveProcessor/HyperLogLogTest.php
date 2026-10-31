<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\ArchiveProcessor;

use Piwik\ArchiveProcessor\HyperLogLog;

/**
 * @group Core
 */
class HyperLogLogTest extends \PHPUnit\Framework\TestCase
{
    private const PRECISION = 14;

    /**
     * @dataProvider getCounts
     */
    public function testEstimateIsWithinThreeStandardErrors(int $count)
    {
        $estimate = $this->sketchOf(0, $count)->estimate();

        $standardError = 1.04 / sqrt(1 << self::PRECISION);
        $this->assertEqualsWithDelta($count, $estimate, max(1, 3 * $standardError * $count));
    }

    public function getCounts(): array
    {
        return [[100], [10000], [1000000]];
    }

    public function testEstimateHasNoBiasJustAboveTheSmallCountRange()
    {
        // the raw HLL formula used above 2.5 * m (about 41k) reads about 1.5% high around 45k
        $count = 45000;
        $errors = [];
        for ($set = 0; $set < 20; $set++) {
            $errors[] = ($this->sketchOf($set * $count, $count)->estimate() - $count) / $count;
        }

        $this->assertEqualsWithDelta(0, array_sum($errors) / count($errors), 0.006);
    }

    public function testEmptySketchEstimatesZero()
    {
        $this->assertSame(0, (new HyperLogLog(self::PRECISION))->estimate());
    }

    public function testMergeEqualsSketchOfUnion()
    {
        // two overlapping sets: 0..59999 and 40000..99999
        $merged = $this->sketchOf(0, 60000);
        $merged->merge($this->sketchOf(40000, 60000));

        $this->assertSame($this->sketchOf(0, 100000)->toString(), $merged->toString());
    }

    public function testStringRoundTrip()
    {
        $sketch = $this->sketchOf(0, 5000);
        $restored = HyperLogLog::fromString($sketch->toString());

        $this->assertSame(self::PRECISION, $restored->getPrecision());
        $this->assertSame($sketch->toString(), $restored->toString());
        $this->assertSame($sketch->estimate(), $restored->estimate());
    }

    public function testReduceToEqualsSketchBuiltAtLowerPrecision()
    {
        $reduced = $this->sketchOf(0, 50000)->reduceTo(12);

        $this->assertSame($this->sketchOf(0, 50000, 12)->toString(), $reduced->toString());
    }

    public function testReduceToKeepsTopRankRegistersCorrect()
    {
        // an all-zero remainder is too rare to hit with real IDs, so build it directly: the dropped index
        // bits become the lowest bits of the new remainder
        $sketch = HyperLogLog::fromBucketMinimums(8, [[0b10110001, 0], [0b00000010, 0]]);

        $expected = HyperLogLog::fromBucketMinimums(4, [[0b0001, 0b1011], [0b0010, 0]]);
        $this->assertSame($expected->toString(), $sketch->reduceTo(4)->toString());
    }

    public function testReduceToRejectsHigherPrecision()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new HyperLogLog(12))->reduceTo(14);
    }

    public function testMergeOfDifferentPrecisionsUsesTheLowerOne()
    {
        $merged = $this->sketchOf(0, 60000, 14);
        $merged->merge($this->sketchOf(40000, 60000, 12));

        $this->assertSame(12, $merged->getPrecision());
        $this->assertSame($this->sketchOf(0, 100000, 12)->toString(), $merged->toString());
    }

    public function testFromStringRejectsTruncatedSketch()
    {
        $this->expectException(\InvalidArgumentException::class);

        HyperLogLog::fromString(substr($this->sketchOf(0, 100)->toString(), 0, -1));
    }

    public function testFromStringRejectsRankTheSketchCannotHold()
    {
        $this->expectException(\InvalidArgumentException::class);

        // at precision 14 the highest rank is 64 - 14 + 1 = 51
        HyperLogLog::fromString(substr($this->sketchOf(0, 100)->toString(), 0, -1) . chr(52));
    }

    /**
     * Hashes the IDs the same way the SQL bucket query does (first 64 bits of MD5) and keeps
     * the minimum remainder per bucket.
     */
    private function sketchOf(int $firstId, int $count, int $precision = self::PRECISION): HyperLogLog
    {
        $mask = (1 << $precision) - 1;
        $remainderMask = (1 << (64 - $precision)) - 1;
        $minimums = [];

        for ($id = $firstId; $id < $firstId + $count; $id++) {
            $hash = unpack('J', md5((string) $id, true))[1];
            $bucket = $hash & $mask;
            $remainder = ($hash >> $precision) & $remainderMask;

            if (!isset($minimums[$bucket]) || $remainder < $minimums[$bucket]) {
                $minimums[$bucket] = $remainder;
            }
        }

        $rows = [];
        foreach ($minimums as $bucket => $minimum) {
            $rows[] = [$bucket, $minimum];
        }

        return HyperLogLog::fromBucketMinimums($precision, $rows);
    }
}

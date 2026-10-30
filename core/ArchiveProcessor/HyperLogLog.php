<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

/**
 * A HyperLogLog sketch: estimates how many distinct values were seen, using 2^precision small
 * registers instead of remembering every value. Two sketches merge into the sketch of the union,
 * which lets a period's estimate be built from its sub-periods without querying the logs again.
 *
 * Each register is the highest "rank" (leading zeros + 1) of the 64-bit hashes that fell into it.
 *
 * @internal
 */
class HyperLogLog
{
    public const MIN_PRECISION = 4;
    public const MAX_PRECISION = 18;

    /**
     * @var int[] register values, 0-indexed
     */
    private array $registers;

    public function __construct(private int $precision)
    {
        if ($precision < self::MIN_PRECISION || $precision > self::MAX_PRECISION) {
            throw new \InvalidArgumentException('Unsupported HyperLogLog precision: ' . $precision);
        }

        $this->registers = array_fill(0, 1 << $precision, 0);
    }

    /**
     * Builds a sketch from rows of [bucket, minimum of the remaining hash bits in that bucket],
     * which is what the SQL bucket query returns. The smallest remainder has the most leading zeros,
     * so it gives the bucket's highest rank.
     *
     * @param iterable<array{0: int|string, 1: int|string}> $bucketMinimums
     */
    public static function fromBucketMinimums(int $precision, iterable $bucketMinimums): self
    {
        $sketch = new self($precision);
        $remainderBits = 64 - $precision;

        foreach ($bucketMinimums as [$bucket, $minimum]) {
            $rank = self::rank((int) $minimum, $remainderBits);
            $bucket = (int) $bucket;

            if ($rank > $sketch->registers[$bucket]) {
                $sketch->registers[$bucket] = $rank;
            }
        }

        return $sketch;
    }

    public static function fromString(string $value): self
    {
        $precision = ord($value[0] ?? "\0");
        $sketch = new self($precision);

        if (strlen($value) !== 1 + (1 << $precision)) {
            throw new \InvalidArgumentException('Invalid HyperLogLog sketch length');
        }

        $sketch->registers = array_values(unpack('C*', substr($value, 1)));
        if (max($sketch->registers) > 64 - $precision + 1) {
            throw new \InvalidArgumentException('Invalid HyperLogLog sketch register');
        }

        return $sketch;
    }

    /**
     * One byte for the precision, then one byte per register.
     */
    public function toString(): string
    {
        return chr($this->precision) . pack('C*', ...$this->registers);
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * Merges another sketch into this one, so this one becomes the sketch of both sets together.
     * Sketches of different precisions merge at the lower one, so changing the precision setting
     * doesn't break periods that span the change.
     */
    public function merge(self $other): void
    {
        if ($other->precision > $this->precision) {
            $other = $other->reduceTo($this->precision);
        } elseif ($other->precision < $this->precision) {
            $reduced = $this->reduceTo($other->precision);
            $this->precision = $reduced->precision;
            $this->registers = $reduced->registers;
        }

        foreach ($other->registers as $index => $rank) {
            if ($rank > $this->registers[$index]) {
                $this->registers[$index] = $rank;
            }
        }
    }

    /**
     * Returns the sketch this one would have been at a lower precision: the same as building it from
     * the same values at that precision. The index bits that are dropped become the lowest bits of the
     * remainder, so they only change the rank of registers whose remainder was all zeros.
     */
    public function reduceTo(int $precision): self
    {
        if ($precision > $this->precision) {
            throw new \InvalidArgumentException('Cannot increase the precision of a HyperLogLog sketch');
        }

        $reduced = new self($precision);
        $mask = (1 << $precision) - 1;
        $topRank = 64 - $this->precision + 1;

        foreach ($this->registers as $index => $rank) {
            if ($rank === $topRank) {
                $rank = self::rank($index >> $precision, 64 - $precision);
            }

            if ($rank > $reduced->registers[$index & $mask]) {
                $reduced->registers[$index & $mask] = $rank;
            }
        }

        return $reduced;
    }

    /**
     * Ertl's improved estimator ("New cardinality estimation algorithms for HyperLogLog sketches", 2017).
     * One formula covers small and large counts, so there is no switch point and no bias just above it.
     */
    public function estimate(): int
    {
        $m = 1 << $this->precision;
        $q = 64 - $this->precision;

        // how many registers hold each rank, 0 to q + 1
        $counts = array_fill(0, $q + 2, 0);
        foreach ($this->registers as $rank) {
            $counts[$rank]++;
        }

        if ($counts[0] === $m) {
            return 0;
        }

        $z = $m * self::tau(1 - $counts[$q + 1] / $m);
        for ($k = $q; $k >= 1; $k--) {
            $z = 0.5 * ($z + $counts[$k]);
        }
        $z += $m * self::sigma($counts[0] / $m);

        $estimate = $m * $m / (2 * log(2)) / $z;

        // only reachable with a broken hash (eg every visitor in the top rank): don't overflow the int
        return $estimate >= PHP_INT_MAX ? PHP_INT_MAX : (int) round($estimate);
    }

    private static function sigma(float $x): float
    {
        $y = 1.0;
        $z = $x;
        do {
            $x *= $x;
            $previous = $z;
            $z += $x * $y;
            $y += $y;
        } while ($z !== $previous);

        return $z;
    }

    private static function tau(float $x): float
    {
        if ($x == 0.0 || $x == 1.0) {
            return 0.0;
        }

        $y = 1.0;
        $z = 1 - $x;
        do {
            $x = sqrt($x);
            $previous = $z;
            $y *= 0.5;
            $z -= (1 - $x) ** 2 * $y;
        } while ($z !== $previous);

        return $z / 3;
    }

    // rank = leading zeros + 1 of a remainder that is $bits wide
    private static function rank(int $remainder, int $bits): int
    {
        return $bits - ($remainder === 0 ? 0 : strlen(decbin($remainder))) + 1;
    }
}

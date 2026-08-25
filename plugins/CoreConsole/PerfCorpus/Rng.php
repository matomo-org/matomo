<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * The only source of randomness in the performance corpus generator.
 *
 * Nothing in the data path may call rand(), mt_rand(), array_rand(), shuffle(), uniqid()
 * or time(): the corpus has to come out identical for a given seed no matter how many
 * workers produced it, and no matter how many times it is regenerated. (The current
 * VisitorGenerator gets this wrong in a way that also corrupts its data - array_rand([1,2,3])
 * returns a key, so its referer_type column holds 0-2 instead of 1-3.)
 *
 * Each entity gets its own independent stream, derived from the run seed plus the entity's
 * identity - Rng::forStream($seed, self::S_VISITOR, $visitorOrdinal). A worker can therefore
 * produce visitor 12,345,678 without having produced any of the visitors before it, which is
 * what lets the work be split arbitrarily across processes.
 *
 * The generator is xoshiro128** with a splitmix32 seeder. Both are 32 bit, chosen because
 * PHP has no unsigned 64 bit integer: a 64 bit generator would silently overflow into float
 * and lose determinism. Every intermediate here stays below 2^62, so it fits in PHP's signed
 * 64 bit int, and results are masked back to 32 bits.
 *
 * Caveat on reproducibility: integer draws are bit-exact anywhere PHP runs. Draws that go
 * through log()/exp()/cos() (gaussian, lognormal, zipf) depend on the platform's libm and
 * can differ in the last bit between architectures. Reproduction is therefore exact on the
 * same machine and statistically identical across machines, which is what the corpus needs.
 */
class Rng
{
    private const MASK32 = 0xFFFFFFFF;
    private const TWO_POW_32 = 4294967296;

    /** Stream namespaces, so two different uses of the same ordinal never share a stream. */
    public const S_SPIKE_DAYS = 1;
    public const S_VISITOR_SHAPE = 2;
    public const S_VISITOR_CONTENT = 3;
    public const S_DAY_SHARD = 4;
    public const S_DICTIONARY = 5;
    public const S_VISIT = 6;

    private int $s0;
    private int $s1;
    private int $s2;
    private int $s3;

    private function __construct(int $s0, int $s1, int $s2, int $s3)
    {
        $this->s0 = $s0;
        $this->s1 = $s1;
        $this->s2 = $s2;
        $this->s3 = $s3;
    }

    /**
     * Builds an independent stream from a list of integer keys. The first key is conventionally
     * the run seed and the second one of the S_* namespaces.
     *
     * @param int ...$keys
     */
    public static function forStream(int ...$keys): self
    {
        $acc = 0x2545F491;

        foreach ($keys as $key) {
            // Fold both halves so keys above 2^32 (a raw seed, a timestamp) still contribute.
            $acc = self::splitmix32($acc ^ ($key & self::MASK32));
            $acc = self::splitmix32($acc ^ (($key >> 32) & self::MASK32));
        }

        $s0 = self::splitmix32($acc);
        $s1 = self::splitmix32($s0);
        $s2 = self::splitmix32($s1);
        $s3 = self::splitmix32($s2);

        if (0 === ($s0 | $s1 | $s2 | $s3)) {
            $s0 = 0x9E3779B9; // an all-zero state is the generator's one fixed point
        }

        return new self($s0, $s1, $s2, $s3);
    }

    /**
     * splitmix32, used only to spread the seed keys over the four state words.
     */
    private static function splitmix32(int $z): int
    {
        $z = ($z + 0x9E3779B9) & self::MASK32;
        $z ^= ($z >> 16);
        $z = ($z * 0x21F0AAAD) & self::MASK32;
        $z ^= ($z >> 15);
        $z = ($z * 0x735A2D97) & self::MASK32;
        $z ^= ($z >> 15);

        return $z & self::MASK32;
    }

    private static function rotl32(int $x, int $k): int
    {
        return (($x << $k) | ($x >> (32 - $k))) & self::MASK32;
    }

    /**
     * xoshiro128** next(). Returns a value in [0, 2^32).
     */
    public function nextUint32(): int
    {
        $result = (self::rotl32(($this->s1 * 5) & self::MASK32, 7) * 9) & self::MASK32;

        $t = ($this->s1 << 9) & self::MASK32;

        $this->s2 ^= $this->s0;
        $this->s3 ^= $this->s1;
        $this->s1 ^= $this->s2;
        $this->s0 ^= $this->s3;
        $this->s2 ^= $t;
        $this->s3 = self::rotl32($this->s3, 11);

        return $result;
    }

    /**
     * Uniform float in [0, 1).
     */
    public function nextFloat(): float
    {
        return $this->nextUint32() / self::TWO_POW_32;
    }

    /**
     * Uniform integer in [$min, $max] inclusive, without modulo bias.
     */
    public function nextInt(int $min, int $max): int
    {
        if ($max <= $min) {
            return $min;
        }

        $range = $max - $min + 1;
        // Largest multiple of $range that fits in 32 bits; anything above it would bias the modulo.
        $limit = intdiv(self::TWO_POW_32, $range) * $range;

        do {
            $draw = $this->nextUint32();
        } while ($draw >= $limit);

        return $min + ($draw % $range);
    }

    /**
     * True with probability $probability.
     */
    public function nextBool(float $probability): bool
    {
        if ($probability <= 0.0) {
            return false;
        }
        if ($probability >= 1.0) {
            return true;
        }

        return $this->nextFloat() < $probability;
    }

    /**
     * Index into a cumulative weight table, e.g. [0.55, 0.95, 0.99, 1.0] for a 55/40/4/1 split.
     * The table must be ascending and end at 1.0. Linear scan: these tables are short and the
     * scan beats a binary search for the sizes used here (device types, action types, referrers).
     */
    public function pickFromCdf(array $cdf): int
    {
        $draw = $this->nextFloat();
        $last = count($cdf) - 1;

        for ($i = 0; $i < $last; $i++) {
            if ($draw < $cdf[$i]) {
                return $i;
            }
        }

        return $last;
    }

    /**
     * Standard normal, Box-Muller. No spare-value cache on purpose: caching would make the
     * draw sequence depend on how many times the caller happened to have called before.
     */
    public function nextGaussian(): float
    {
        $u1 = $this->nextFloat();
        $u2 = $this->nextFloat();

        if ($u1 < 1.0e-12) {
            $u1 = 1.0e-12; // log(0) guard; the draw is uniform so this is vanishingly rare
        }

        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
    }

    /**
     * Lognormal with the given median and shape. Used for inter-visit gaps, actions per visit,
     * page timings and order revenue - everything with a long right tail.
     */
    public function nextLogNormal(float $median, float $sigma): float
    {
        return $median * exp($sigma * $this->nextGaussian());
    }

    /**
     * Rank in [1, $n] under a Zipf-like popularity law (B4: Transitions and the Pages tree need
     * genuinely hot pages, not a flat URL space).
     *
     * This inverts the continuous approximation of the Zipf CDF rather than building a table:
     * a real CDF over the 5M-entry hot pool would cost 40 MB per worker for no visible gain.
     * For exponent 1 the approximation is P(rank <= k) = ln(k+1)/ln(n+1).
     */
    public function nextZipfRank(int $n, float $exponent): int
    {
        if ($n <= 1) {
            return 1;
        }

        $u = $this->nextFloat();

        if (abs($exponent - 1.0) < 1.0e-9) {
            $rank = (int) ceil(exp($u * log($n + 1.0)) - 1.0);
        } else {
            $p = 1.0 - $exponent;
            $rank = (int) ceil((($u * (pow($n, $p) - 1.0)) + 1.0) ** (1.0 / $p));
        }

        if ($rank < 1) {
            return 1;
        }

        return min($rank, $n);
    }

    /**
     * Draw from a bounded Pareto, used for lifetime visit counts (B1).
     */
    public function nextParetoInt(float $exponent, int $min, int $max): int
    {
        $u = $this->nextFloat();
        $value = (int) floor($min * ((1.0 - $u) ** (-1.0 / ($exponent - 1.0))));

        return max($min, min($value, $max));
    }
}

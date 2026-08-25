<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Tracker\Action;

/**
 * The log_action dictionary, laid out as fixed blocks of idaction so that an id can be computed
 * rather than looked up.
 *
 * This is the single biggest reason the existing generator cannot scale: it issues a SELECT
 * against log_action for every action it writes, and an INSERT whenever that misses. Here the
 * whole dictionary is written once, up front, in one deterministic pass, and every worker after
 * that computes idaction as blockBase + index. No lookups, no contention, no duplicates.
 *
 * URL cardinality deliberately models both regimes a high-traffic site shows at once:
 *
 *   the hot pool  - a fixed set of pages that get revisited, drawn Zipf, so Transitions and the
 *                   Pages tree have genuinely hot pages to work with (B4);
 *   the unique tail - URLs carrying an id, one per pageview, which never repeat. This is what
 *                   makes log_action grow without bound on a real site and what makes the Pages
 *                   archiving join expensive (B3). Tail ids are allocated per chunk, so workers
 *                   mint them without coordinating.
 */
class ActionDictionary
{
    /**
     * Fixed blocks, in id order. Each is [key, action type, url_prefix or null].
     * url_prefix 2 means https:// - Matomo strips the scheme into that column.
     */
    private const BLOCKS = [
        ['hotUrl', Action::TYPE_PAGE_URL, 2],
        ['hotTitle', Action::TYPE_PAGE_TITLE, null],
        ['tailTitle', Action::TYPE_PAGE_TITLE, null],
        ['searchKeyword', Action::TYPE_SITE_SEARCH, null],
        ['eventCategory', Action::TYPE_EVENT_CATEGORY, null],
        ['eventAction', Action::TYPE_EVENT_ACTION, null],
        ['eventName', Action::TYPE_EVENT_NAME, null],
        ['outlink', Action::TYPE_OUTLINK, 2],
        ['download', Action::TYPE_DOWNLOAD, 2],
        ['sku', Action::TYPE_ECOMMERCE_ITEM_SKU, null],
        ['productName', Action::TYPE_ECOMMERCE_ITEM_NAME, null],
        ['productCategory', Action::TYPE_ECOMMERCE_ITEM_CATEGORY, null],
    ];

    private const PRODUCT_CATEGORY_COUNT = 500;

    private Profile $profile;

    /** @var array<string,array{base:int,count:int,type:int,prefix:?int}> */
    private array $blocks = [];

    private int $nextId;

    public function __construct(Profile $profile, int $firstId = 1)
    {
        $this->profile = $profile;
        $this->nextId = $firstId;

        $sizes = $this->blockSizes();

        foreach (self::BLOCKS as [$key, $type, $prefix]) {
            $this->blocks[$key] = [
                'base' => $this->nextId,
                'count' => $sizes[$key],
                'type' => $type,
                'prefix' => $prefix,
            ];
            $this->nextId += $sizes[$key];
        }
    }

    private function blockSizes(): array
    {
        $pools = $this->profile->getDictionaryPools();
        $hotPool = $this->profile->getHotPoolSize();

        return [
            'hotUrl' => $hotPool,
            'hotTitle' => $hotPool,
            'tailTitle' => count(Vocabulary::TAIL_TITLES),
            'searchKeyword' => $pools['searchKeywords'],
            'eventCategory' => $pools['eventCategories'],
            'eventAction' => $pools['eventActions'],
            'eventName' => $pools['eventNames'],
            'outlink' => $pools['outlinks'],
            'download' => $pools['downloads'],
            'sku' => $pools['ecommerceSkus'],
            'productName' => $pools['ecommerceSkus'],
            'productCategory' => self::PRODUCT_CATEGORY_COUNT,
        ];
    }

    /**
     * The first idaction after the fixed blocks. Per-chunk unique-URL ranges start here.
     */
    public function getFirstTailId(): int
    {
        return $this->nextId;
    }

    public function getBlockBase(string $key): int
    {
        return $this->blocks[$key]['base'];
    }

    public function getBlockCount(string $key): int
    {
        return $this->blocks[$key]['count'];
    }

    /**
     * idaction for entry $index of a block. Callers pass a 0-based index; the modulo keeps a
     * caller that draws beyond the pool inside it rather than pointing at another block's rows.
     */
    public function id(string $key, int $index): int
    {
        $block = $this->blocks[$key];

        return $block['base'] + ($index % $block['count']);
    }

    /**
     * A uniformly drawn id from a block. Identical to id($key, $rng->nextInt(0, count - 1)) - it
     * just keeps the call sites readable, since every action type needs one.
     */
    public function randomId(string $key, Rng $rng): int
    {
        return $this->id($key, $rng->nextInt(0, $this->blocks[$key]['count'] - 1));
    }

    /**
     * The name behind an idaction, recomputed rather than stored. Needed because log_conversion
     * denormalises the converting page's URL as a string.
     */
    public function nameFor(string $key, int $index): string
    {
        $index %= $this->blocks[$key]['count'];

        switch ($key) {
            case 'hotUrl':
                return Vocabulary::hotUrl($index);
            case 'hotTitle':
                return Vocabulary::hotTitle($index);
            case 'tailTitle':
                return Vocabulary::TAIL_TITLES[$index];
            case 'searchKeyword':
                return Vocabulary::searchKeyword($index);
            case 'eventCategory':
                return Vocabulary::eventCategory($index);
            case 'eventAction':
                return Vocabulary::eventAction($index);
            case 'eventName':
                return Vocabulary::eventName($index);
            case 'outlink':
                return Vocabulary::outlink($index);
            case 'download':
                return Vocabulary::download($index);
            case 'sku':
                return Vocabulary::sku($index);
            case 'productName':
                return Vocabulary::productName($index);
            case 'productCategory':
                return Vocabulary::productCategory($index);
        }

        throw new \InvalidArgumentException('Unknown dictionary block: ' . $key);
    }

    /**
     * Total rows in the fixed part of the dictionary.
     */
    public function getFixedRowCount(): int
    {
        return $this->nextId - $this->blocks['hotUrl']['base'];
    }

    /**
     * Writes the fixed blocks. Called once by the coordinator before any worker starts, because
     * every worker's idaction arithmetic assumes these rows exist.
     *
     * @param callable|null $onProgress called with (rowsWritten, rowsTotal)
     */
    public function insert(RowSink $sink, ?callable $onProgress = null): int
    {
        $written = 0;
        $total = $this->getFixedRowCount();
        $batch = [];

        foreach (self::BLOCKS as [$key, $type, $prefix]) {
            $block = $this->blocks[$key];

            for ($index = 0; $index < $block['count']; $index++) {
                $name = $this->nameFor($key, $index);

                $batch[] = [
                    $block['base'] + $index,
                    $name,
                    self::hash($name),
                    $type,
                    $prefix,
                ];

                if (count($batch) >= 5000) {
                    $sink->write('log_action', self::COLUMNS, $batch);
                    $written += count($batch);
                    $batch = [];

                    if (null !== $onProgress) {
                        $onProgress($written, $total);
                    }
                }
            }
        }

        if (!empty($batch)) {
            $sink->write('log_action', self::COLUMNS, $batch);
            $written += count($batch);
        }

        $sink->flush();

        if (null !== $onProgress) {
            $onProgress($written, $total);
        }

        return $written;
    }

    public const COLUMNS = ['idaction', 'name', 'hash', 'type', 'url_prefix'];

    /**
     * log_action.hash as core computes it. Core does it server-side with MySQL's CRC32() (see
     * Tracker\Model::insertNewAction); PHP's crc32() returns the same value for the same bytes on
     * 64-bit PHP, and the corpus writes the bytes directly, so the two agree.
     */
    public static function hash(string $name): int
    {
        return crc32($name);
    }

    /**
     * Unique-tail URL rows for one chunk, from that chunk's own idaction range.
     *
     * @param int[] $ids the tail ids actually used, in order
     */
    public static function tailRows(int $tailBase, int $count): array
    {
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $id = $tailBase + $index;
            // The id itself seeds the name, so a chunk's URLs are unique across the whole corpus.
            $name = Vocabulary::tailUrl($id);
            $rows[] = [$id, $name, self::hash($name), Action::TYPE_PAGE_URL, 2];
        }

        return $rows;
    }
}

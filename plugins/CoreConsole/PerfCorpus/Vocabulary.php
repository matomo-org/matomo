<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * The word lists and share tables the corpus is built from.
 *
 * Everything here is a pure function of an index, never of a random draw, so a name can be
 * recomputed from an idaction at any time without storing it. That is what lets the load phase
 * reference dictionary entries by id alone.
 *
 * The share tables matter more than they look. The current VisitorGenerator leaves browser,
 * engine, OS, device brand and model as empty strings and geo as a country code with no region or
 * city, which quietly removes most of the GROUP BY cost from archiving - the reports come out
 * empty and the benchmark looks faster than production ever would.
 */
class Vocabulary
{
    public const SECTIONS = [
        'news', 'sport', 'business', 'travel', 'culture', 'science', 'health', 'opinion',
        'weather', 'technology', 'politics', 'education', 'lifestyle', 'food', 'motoring',
        'property', 'jobs', 'events', 'community', 'archive',
    ];

    public const CATEGORY_WORDS = [
        'local', 'national', 'global', 'daily', 'weekly', 'live', 'analysis', 'review',
        'guide', 'feature', 'report', 'update', 'briefing', 'explainer', 'interview',
        'gallery', 'video', 'podcast', 'column', 'letters', 'ranking', 'preview',
        'results', 'schedule', 'profile',
    ];

    public const SLUG_WORDS = [
        'city', 'council', 'budget', 'plan', 'season', 'record', 'market', 'growth', 'launch',
        'study', 'report', 'debate', 'reform', 'project', 'festival', 'transport', 'housing',
        'energy', 'climate', 'research', 'funding', 'safety', 'traffic', 'schools', 'hospital',
        'election', 'contract', 'survey', 'strategy', 'network', 'service', 'programme',
    ];

    public const TAIL_TITLES = [
        'Order confirmation', 'Search results', 'Document viewer', 'Booking details',
        'Ticket summary', 'Invoice', 'Account activity', 'Download ready', 'Session detail',
        'Report export',
    ];

    /** Country share, roughly a European-heavy media site. Cumulative shares are built at runtime. */
    public const COUNTRIES = [
        ['de', 0.25], ['fr', 0.15], ['us', 0.12], ['gb', 0.08], ['nl', 0.05], ['es', 0.045],
        ['it', 0.04], ['pl', 0.03], ['at', 0.028], ['ch', 0.025], ['be', 0.022], ['se', 0.02],
        ['cz', 0.018], ['dk', 0.015], ['pt', 0.014], ['no', 0.012], ['fi', 0.011], ['ie', 0.01],
        ['ro', 0.01], ['hu', 0.009], ['gr', 0.008], ['ca', 0.008], ['au', 0.007], ['br', 0.007],
        ['in', 0.006], ['jp', 0.005], ['mx', 0.005], ['tr', 0.005], ['za', 0.004], ['other', 0.0],
    ];

    /**
     * City name stems. A ~2k city pool is built by combining these with a numeric suffix per
     * country, which gives the location_city column realistic cardinality without shipping a
     * geo database. Berlin is first so the "city == Berlin" segment has a fixed target.
     */
    public const CITY_STEMS = [
        'Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Paris', 'Lyon',
        'Marseille', 'Toulouse', 'London', 'Manchester', 'Birmingham', 'Leeds', 'New York',
        'Chicago', 'Houston', 'Phoenix', 'Amsterdam', 'Rotterdam', 'Madrid', 'Barcelona',
        'Rome', 'Milan', 'Warsaw', 'Krakow', 'Vienna', 'Graz', 'Zurich', 'Geneva',
        'Brussels', 'Antwerp', 'Stockholm', 'Gothenburg', 'Prague', 'Brno', 'Copenhagen',
        'Aarhus', 'Lisbon', 'Porto', 'Oslo', 'Bergen', 'Helsinki', 'Tampere', 'Dublin', 'Cork',
    ];

    /** Browser share, cumulative built at runtime: name, engine, os code, share. */
    public const BROWSERS = [
        ['CH', 'Blink', 0.55], ['SF', 'WebKit', 0.18], ['FF', 'Gecko', 0.06],
        ['CE', 'Blink', 0.06], ['OP', 'Blink', 0.035], ['SM', 'Blink', 0.03],
        ['MF', 'Blink', 0.025], ['IE', 'Trident', 0.02], ['BR', 'Blink', 0.02],
        ['PU', 'WebKit', 0.02],
    ];

    /** Matomo device types: 0 desktop, 1 smartphone, 2 tablet, 3 feature phone, 5 console. */
    public const DEVICE_TYPES = [[0, 0.55], [1, 0.40], [2, 0.04], [3, 0.005], [5, 0.005]];

    public const DESKTOP_OS = ['WIN', 'MAC', 'LIN', 'CRO'];
    public const MOBILE_OS = ['AND', 'IOS'];

    public const MOBILE_BRANDS = [
        'Apple', 'Samsung', 'Xiaomi', 'Google', 'Huawei', 'OnePlus', 'Oppo', 'Motorola',
        'Nokia', 'Sony',
    ];

    /**
     * Real user agent strings, for the churn test only.
     *
     * The corpus writes config_browser_* and config_device_* directly, but the churn test goes
     * through the actual tracker, and there device detection is done by DeviceDetector parsing
     * this string. A made-up user agent would be classified as unknown and would also skip the
     * most expensive piece of CPU work a real tracking request does.
     */
    public const USER_AGENTS = [
        // desktop
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:132.0) Gecko/20100101 Firefox/132.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0',
        // smartphone
        'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 14; 23021RAAEG) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/130.0.0.0 Mobile/15E148 Safari/604.1',
        // tablet
        'Mozilla/5.0 (iPad; CPU OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 13; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
    ];

    public const RESOLUTIONS = [
        '1920x1080', '1366x768', '1440x900', '2560x1440', '1536x864', '1280x720', '3840x2160',
        '390x844', '414x896', '360x800', '412x915', '393x873', '768x1024', '810x1080',
    ];

    public const LANGUAGES = [
        'de', 'de-de', 'fr', 'fr-fr', 'en', 'en-gb', 'en-us', 'nl', 'es', 'it', 'pl', 'pt',
        'sv', 'da', 'cs', 'fi', 'no', 'hu', 'ro', 'el',
    ];

    public const SEARCH_ENGINES = [
        'Google', 'Bing', 'DuckDuckGo', 'Ecosia', 'Yahoo!', 'Qwant', 'Brave', 'Startpage',
        'Yandex', 'Baidu', 'Seznam', 'Naver', 'Mojeek', 'Searx', 'Presearch',
    ];

    public const CAMPAIGN_MEDIUMS = ['cpc', 'email', 'social', 'display', 'affiliate', 'referral'];
    public const CAMPAIGN_SOURCES = ['newsletter', 'partner', 'adwords', 'meta', 'linkedin', 'x'];

    public const EVENT_CATEGORIES_FIXED = ['checkout', 'video', 'download', 'signup', 'share'];

    /** Referrer types, cumulative: 1 direct, 2 search, 3 website, 6 campaign. */
    public const REFERRER_TYPE_CDF = [0.40, 0.65, 0.85, 1.00];
    public const REFERRER_TYPES = [1, 2, 3, 6];

    /**
     * Turns a [value, share] table into a cumulative distribution usable with Rng::pickFromCdf.
     */
    public static function toCdf(array $table, int $shareIndex): array
    {
        $total = 0.0;
        foreach ($table as $row) {
            $total += $row[$shareIndex];
        }

        $cdf = [];
        $running = 0.0;

        foreach ($table as $row) {
            $running += $row[$shareIndex] / $total;
            $cdf[] = $running;
        }

        $cdf[count($cdf) - 1] = 1.0;

        return $cdf;
    }

    /**
     * A hot page URL, as Matomo stores it in log_action.name: no protocol, because that lives in
     * url_prefix. Hierarchical so the Pages report has a real tree to build.
     */
    public static function hotUrl(int $index): string
    {
        $section = self::SECTIONS[$index % count(self::SECTIONS)];
        $category = self::CATEGORY_WORDS[intdiv($index, count(self::SECTIONS)) % count(self::CATEGORY_WORDS)];
        $slugA = self::SLUG_WORDS[intdiv($index, 97) % count(self::SLUG_WORDS)];
        $slugB = self::SLUG_WORDS[intdiv($index, 1543) % count(self::SLUG_WORDS)];

        return sprintf('example.org/%s/%s/%s-%s-%d', $section, $category, $slugA, $slugB, $index);
    }

    public static function hotTitle(int $index): string
    {
        $slugA = self::SLUG_WORDS[intdiv($index, 97) % count(self::SLUG_WORDS)];
        $slugB = self::SLUG_WORDS[intdiv($index, 1543) % count(self::SLUG_WORDS)];
        $section = self::SECTIONS[$index % count(self::SECTIONS)];

        return sprintf('%s %s - %s', ucfirst($slugA), $slugB, ucfirst($section));
    }

    /**
     * An effectively unique URL: an id in the path or query string. These are what make
     * log_action grow without bound on real sites (B3).
     */
    public static function tailUrl(int $index): string
    {
        switch ($index % 4) {
            case 0:
                return sprintf('example.org/order/confirmation?id=%d', 100000 + $index);
            case 1:
                return sprintf('example.org/search?q=%s-%d', self::SLUG_WORDS[$index % 32], $index);
            case 2:
                return sprintf('example.org/document/%08x-%04x', $index, $index % 65535);
            default:
                return sprintf('example.org/booking/%d/summary', 500000 + $index);
        }
    }

    public static function searchKeyword(int $index): string
    {
        $a = self::SLUG_WORDS[$index % count(self::SLUG_WORDS)];
        $b = self::CATEGORY_WORDS[intdiv($index, count(self::SLUG_WORDS)) % count(self::CATEGORY_WORDS)];

        return sprintf('%s %s %d', $a, $b, $index % 997);
    }

    public static function eventCategory(int $index): string
    {
        if ($index < count(self::EVENT_CATEGORIES_FIXED)) {
            return self::EVENT_CATEGORIES_FIXED[$index];
        }

        return sprintf('category-%d', $index);
    }

    public static function eventAction(int $index): string
    {
        return sprintf('%s-%d', self::CATEGORY_WORDS[$index % count(self::CATEGORY_WORDS)], $index);
    }

    public static function eventName(int $index): string
    {
        return sprintf('%s %d', self::SLUG_WORDS[$index % count(self::SLUG_WORDS)], $index);
    }

    public static function outlink(int $index): string
    {
        return sprintf('partner-%d.example.com/landing/%d', $index % 500, $index);
    }

    public static function download(int $index): string
    {
        return sprintf('example.org/files/%s-%d.pdf', self::SLUG_WORDS[$index % 32], $index);
    }

    public static function sku(int $index): string
    {
        return sprintf('SKU-%06d', $index);
    }

    public static function productName(int $index): string
    {
        return sprintf('%s %s %d', ucfirst(self::CATEGORY_WORDS[$index % 25]), self::SLUG_WORDS[$index % 32], $index);
    }

    public static function productCategory(int $index): string
    {
        return sprintf('%s/%s', self::SECTIONS[$index % 20], self::CATEGORY_WORDS[$index % 25]);
    }

    public static function referrerWebsite(int $index): string
    {
        return sprintf('referrer-%d.example.net', $index);
    }

    public static function campaignName(int $index): string
    {
        return sprintf('campaign-%s-%d', self::CATEGORY_WORDS[$index % 25], $index);
    }

    /**
     * City for a given pool index, paired with a country so the two stay consistent.
     */
    public static function city(int $index): string
    {
        $stem = self::CITY_STEMS[$index % count(self::CITY_STEMS)];
        $suffix = intdiv($index, count(self::CITY_STEMS));

        return 0 === $suffix ? $stem : $stem . ' ' . $suffix;
    }
}

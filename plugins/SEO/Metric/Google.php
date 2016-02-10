<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Http;
use Piwik\NumberFormatter;
use Piwik\Plugins\Referrers\SearchEngine;
use Psr\Log\LoggerInterface;

/**
 * Retrieves Google PageRank.
 */
class Google implements MetricsProvider
{
    const SEARCH_URL = 'http://www.google.com/search?hl=en&q=site%3A';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getMetrics($domain)
    {
        $pageCount = $this->fetchIndexedPagesCount($domain);
        $pageRank = $this->fetchPageRank($domain);

        $logo = SearchEngine::getInstance()->getLogoFromUrl('http://google.com');

        return array(
            new Metric('google-index', 'SEO_Google_IndexedPages', $pageCount, $logo, null, null, 'General_Pages'),
            new Metric('pagerank', 'Google PageRank', $pageRank, $logo, null, null, '/10'),
        );
    }

    public function fetchIndexedPagesCount($domain)
    {
        $url = self::SEARCH_URL . urlencode($domain);

        try {
            $response = str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));

            if (preg_match('#([0-9\,]+) results#i', $response, $p)) {
                return NumberFormatter::getInstance()->formatNumber((int)str_replace(',', '', $p[1]));
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Google search SEO stats: {message}', array('message' => $e->getMessage()));
            return null;
        }
    }

    public function fetchPageRank($domain)
    {
        $chwrite = $this->checkHash($this->hashURL($domain));

        $url = "http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=" . $chwrite . "&features=Rank&q=info:" . $domain . "&num=100&filter=0";

        try {
            $response = Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']);

            preg_match('#Rank_[0-9]:[0-9]:([0-9]+){1,}#si', $response, $p);

            return isset($p[1]) ? $p[1] : null;
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Google PageRank for SEO stats: {message}', array('message' => $e->getMessage()));
            return null;
        }
    }

    /**
     * Generate a hash for a url
     *
     * @param string $string
     * @return int
     */
    private function hashURL($string)
    {
        $Check1 = $this->strToNum($string, 0x1505, 0x21);
        $Check2 = $this->strToNum($string, 0, 0x1003F);

        $Check1 >>= 2;
        $Check1 = (($Check1 >> 4) & 0x3FFFFC0) | ($Check1 & 0x3F);
        $Check1 = (($Check1 >> 4) & 0x3FFC00) | ($Check1 & 0x3FF);
        $Check1 = (($Check1 >> 4) & 0x3C000) | ($Check1 & 0x3FFF);

        $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) << 2) | ($Check2 & 0xF0F);
        $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000);

        return ($T1 | $T2);
    }

    /**
     * Generate a checksum for the hash string
     *
     * @param int $hashnum
     * @return string
     */
    private function checkHash($hashnum)
    {
        $CheckByte = 0;
        $Flag = 0;

        $HashStr = sprintf('%u', $hashnum);
        $length = strlen($HashStr);

        for ($i = $length - 1; $i >= 0; $i--) {
            $Re = $HashStr{$i};
            if (1 === ($Flag % 2)) {
                $Re += $Re;
                $Re = (int)($Re / 10) + ($Re % 10);
            }
            $CheckByte += $Re;
            $Flag++;
        }

        $CheckByte %= 10;
        if (0 !== $CheckByte) {
            $CheckByte = 10 - $CheckByte;
            if (1 === ($Flag % 2)) {
                if (1 === ($CheckByte % 2)) {
                    $CheckByte += 9;
                }
                $CheckByte >>= 1;
            }
        }

        return '7' . $CheckByte . $HashStr;
    }

    /**
     * Convert numeric string to int
     *
     * @param string $Str
     * @param int $Check
     * @param int $Magic
     * @return int
     */
    private function strToNum($Str, $Check, $Magic)
    {
        $Int32Unit = 4294967296; // 2^32

        $length = strlen($Str);
        for ($i = 0; $i < $length; $i++) {
            $Check *= $Magic;
            // If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
            // the result of converting to integer is undefined
            // refer to http://www.php.net/manual/en/language.types.integer.php
            if ($Check >= $Int32Unit) {
                $Check = ($Check - $Int32Unit * (int)($Check / $Int32Unit));
                //if the check less than -2^31
                $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
            }
            $Check += ord($Str{$i});
        }
        return $Check;
    }
}

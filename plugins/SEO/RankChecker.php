<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO;

use Exception;
use Piwik\Http;
use Piwik\Log;
use Piwik\MetricsFormatter;

/**
 * The functions below are derived/adapted from GetRank.org's
 * Free PageRank Script v2.0, released under GPL.
 *
 * @copyright Copyright (C) 2007 - 2010 GetRank.Org  All rights reserved.
 * @link http://www.getrank.org/free-pagerank-script/
 * @license GPL
 */
class RankChecker
{
    private $url;
    private $majesticInfo = null;

    public function __construct($url)
    {
        $this->url = self::extractDomainFromUrl($url);
    }

    /**
     * Extract domain from URL as the web services generally
     * expect only a domain name (i.e., no protocol, port, path, query, etc).
     *
     * @param string $url
     * @return string
     */
    public static function extractDomainFromUrl($url)
    {
        return preg_replace(
            array(
                 '~^https?\://~si', // strip protocol
                 '~[/:#?;%&].*~', // strip port, path, query, anchor, etc
                 '~\.$~', // trailing period
            ),
            '', $url);
    }

    /**
     * Web service proxy that retrieves the content at the specified URL
     *
     * @param string $url
     * @return string
     */
    private function getPage($url)
    {
        try {
            return str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Returns the google page rank for the current url
     *
     * @return int
     */
    public function getPageRank()
    {
        $chwrite = $this->CheckHash($this->HashURL($this->url));

        $url = "http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=" . $chwrite . "&features=Rank&q=info:" . $this->url . "&num=100&filter=0";
        $data = $this->getPage($url);
        preg_match('#Rank_[0-9]:[0-9]:([0-9]+){1,}#si', $data, $p);
        $value = isset($p[1]) ? $p[1] : 0;

        return $value;
    }

    /**
     * Returns the alexa traffic rank for the current url
     *
     * @return int
     */
    public function getAlexaRank()
    {
        $xml = @simplexml_load_string($this->getPage('http://data.alexa.com/data?cli=10&url=' . urlencode($this->url)));
        return $xml ? $xml->SD->POPULARITY['TEXT'] : '';
    }

    /**
     * Returns the number of Dmoz.org entries for the current url
     *
     * @return int
     */
    public function getDmoz()
    {
        $url = 'http://www.dmoz.org/search?q=' . urlencode($this->url);
        $data = $this->getPage($url);
        preg_match('#Open Directory Sites[^\(]+\([0-9]-[0-9]+ of ([0-9]+)\)#', $data, $p);
        if (!empty($p[1])) {
            return (int)$p[1];
        }
        return 0;
    }

    /**
     * Returns the number of pages google holds in it's index for the current url
     *
     * @return int
     */
    public function getIndexedPagesGoogle()
    {
        $url = 'http://www.google.com/search?hl=en&q=site%3A' . urlencode($this->url);
        $data = $this->getPage($url);
        if (preg_match('#([0-9\,]+) results#i', $data, $p)) {
            $indexedPages = (int)str_replace(',', '', $p[1]);
            return $indexedPages;
        }
        return 0;
    }

    /**
     * Returns the number of pages bing holds in it's index for the current url
     *
     * @return int
     */
    public function getIndexedPagesBing()
    {
        $url = 'http://www.bing.com/search?mkt=en-US&q=site%3A' . urlencode($this->url);
        $data = $this->getPage($url);
        if (preg_match('#([0-9\,]+) results#i', $data, $p)) {
            return (int)str_replace(',', '', $p[1]);
        }
        return 0;
    }

    /**
     * Returns the domain age for the current url
     *
     * @return int
     */
    public function getAge()
    {
        $ageArchiveOrg = $this->_getAgeArchiveOrg();
        $ageWhoIs = $this->_getAgeWhoIs();
        $ageWhoisCom = $this->_getAgeWhoisCom();

        $ages = array();

        if ($ageArchiveOrg > 0) {
            $ages[] = $ageArchiveOrg;
        }

        if ($ageWhoIs > 0) {
            $ages[] = $ageWhoIs;
        }

        if ($ageWhoisCom > 0) {
            $ages[] = $ageWhoisCom;
        }

        if (count($ages) > 1) {
            $maxAge = min($ages);
        } else {
            $maxAge = array_shift($ages);
        }

        if ($maxAge) {
            return MetricsFormatter::getPrettyTimeFromSeconds(time() - $maxAge);
        }
        return false;
    }

    /**
     * Returns the number backlinks that link to the current site.
     *
     * @return int
     */
    public function getExternalBacklinkCount()
    {
        try {
            $majesticInfo = $this->getMajesticInfo();
            return $majesticInfo['backlink_count'];
        } catch (Exception $e) {
            Log::info($e);
            return 0;
        }
    }

    /**
     * Returns the number of referrer domains that link to the current site.
     *
     * @return int
     */
    public function getReferrerDomainCount()
    {
        try {
            $majesticInfo = $this->getMajesticInfo();
            return $majesticInfo['referrer_domains_count'];
        } catch (Exception $e) {
            Log::info($e);
            return 0;
        }
    }

    /**
     * Returns the domain age archive.org lists for the current url
     *
     * @return int
     */
    protected function _getAgeArchiveOrg()
    {
        $url = str_replace('www.', '', $this->url);
        $data = @$this->getPage('http://wayback.archive.org/web/*/' . urlencode($url));
        preg_match('#<a href=\"([^>]*)' . preg_quote($url) . '/\">([^<]*)<\/a>#', $data, $p);
        if (!empty($p[2])) {
            $value = strtotime($p[2]);
            if ($value === false) {
                return 0;
            }
            return $value;
        }
        return 0;
    }

    /**
     * Returns the domain age who.is lists for the current url
     *
     * @return int
     */
    protected function _getAgeWhoIs()
    {
        $url = preg_replace('/^www\./', '', $this->url);
        $url = 'http://www.who.is/whois/' . urlencode($url);
        $data = $this->getPage($url);
        preg_match('#(?:Creation Date|Created On|created|Registered on)\.*:\s*([ \ta-z0-9\/\-:\.]+)#si', $data, $p);
        if (!empty($p[1])) {
            $value = strtotime(trim($p[1]));
            if ($value === false) {
                return 0;
            }
            return $value;
        }
        return 0;
    }

    /**
     * Returns the domain age whois.com lists for the current url
     *
     * @return int
     */
    protected function _getAgeWhoisCom()
    {
        $url = preg_replace('/^www\./', '', $this->url);
        $url = 'http://www.whois.com/whois/' . urlencode($url);
        $data = $this->getPage($url);
        preg_match('#(?:Creation Date|Created On|created):\s*([ \ta-z0-9\/\-:\.]+)#si', $data, $p);
        if (!empty($p[1])) {
            $value = strtotime(trim($p[1]));
            if ($value === false) {
                return 0;
            }
            return $value;
        }
        return 0;
    }

    /**
     * Convert numeric string to int
     *
     * @see getPageRank()
     *
     * @param string $Str
     * @param int $Check
     * @param int $Magic
     * @return int
     */
    private function StrToNum($Str, $Check, $Magic)
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

    /**
     * Generate a hash for a url
     *
     * @see getPageRank()
     *
     * @param string $String
     * @return int
     */
    private function HashURL($String)
    {
        $Check1 = $this->StrToNum($String, 0x1505, 0x21);
        $Check2 = $this->StrToNum($String, 0, 0x1003F);

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
     * @see getPageRank()
     *
     * @param int $Hashnum
     * @return string
     */
    private function CheckHash($Hashnum)
    {
        $CheckByte = 0;
        $Flag = 0;

        $HashStr = sprintf('%u', $Hashnum);
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

    private function getMajesticInfo()
    {
        if ($this->majesticInfo === null) {
            $client = new MajesticClient();
            $this->majesticInfo = $client->getBacklinkStats($this->url);
        }

        return $this->majesticInfo;
    }
}

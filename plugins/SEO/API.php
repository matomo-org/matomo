<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO;

use Piwik\Cache;
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * @see plugins/Referrers/functions.php
 * @method static \Piwik\Plugins\SEO\API getInstance()
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

/**
 * The SEO API lets you access a list of SEO metrics for the specified URL: Google Pagerank, Goolge/Bing indexed pages
 * Alexa Rank, age of the Domain name and count of DMOZ entries.
 *
 * @method static \Piwik\Plugins\SEO\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns SEO statistics for a URL.
     *
     * @param string $url URL to request SEO stats for
     * @return DataTable
     */
    public function getRank($url)
    {
        Piwik::checkUserHasSomeViewAccess();

        $data = $this->getCachedRanks($url);

        $translated = array();
        if (!empty($data)) {
            foreach ($data as $title => $rank) {
                $translated[Piwik::translate($title)] = $rank;
            }
        }

        return DataTable::makeFromIndexedArray($translated);
    }

    private function getCachedRanks($url)
    {
        $cacheId = 'SEO_getRank_' . md5($url);

        $cache = Cache::getLazyCache();
        $data  = $cache->fetch($cacheId);

        if (empty($data)) {
            $data = $this->getRanks($url);

            $cache->save($cacheId, $data, 60 * 60 * 6);
        }

        return $data;
    }

    private function getRanks($url)
    {
        $rank = new RankChecker($url);

        $linkToMajestic = MajesticClient::getLinkForUrl($url);

        $data = array(
            'Google PageRank' => array(
                'rank' => $rank->getPageRank(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://google.com'),
                'id'   => 'pagerank'
            ),
            'SEO_Google_IndexedPages' => array(
                'rank' => $rank->getIndexedPagesGoogle(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://google.com'),
                'id'   => 'google-index',
            ),
            'SEO_Bing_IndexedPages' => array(
                'rank' => $rank->getIndexedPagesBing(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://bing.com'),
                'id'   => 'bing-index',
            ),
            'SEO_AlexaRank' => array(
                'rank' => $rank->getAlexaRank() . '',
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://alexa.com'),
                'id'   => 'alexa',
            ),
            'SEO_DomainAge' => array(
                'rank' => $rank->getAge(),
                'logo' => 'plugins/SEO/images/whois.png',
                'id'   => 'domain-age',
            ),
            'SEO_ExternalBacklinks' => array(
                'rank' => $rank->getExternalBacklinkCount(),
                'logo' => 'plugins/SEO/images/majesticseo.png',
                'logo_link' => $linkToMajestic,
                'logo_tooltip' => Piwik::translate('SEO_ViewBacklinksOnMajesticSEO'),
                'id' => 'external-backlinks',
            ),
            'SEO_ReferrerDomains' => array(
                'rank' => $rank->getReferrerDomainCount(),
                'logo' => 'plugins/SEO/images/majesticseo.png',
                'logo_link' => $linkToMajestic,
                'logo_tooltip' => Piwik::translate('SEO_ViewBacklinksOnMajesticSEO'),
                'id' => 'referrer-domains',
            ),
        );

        // Add DMOZ only if > 0 entries found
        $dmozRank = array(
            'rank' => $rank->getDmoz(),
            'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://dmoz.org'),
            'id'   => 'dmoz',
        );

        if ($dmozRank['rank'] > 0) {
            $data['SEO_Dmoz'] = $dmozRank;
        }

        return $data;
    }
}

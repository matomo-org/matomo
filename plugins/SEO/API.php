<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO;

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
        $rank = new RankChecker($url);

        $linkToMajestic = MajesticClient::getLinkForUrl($url);

        $data = array(
            'Google PageRank'                          => array(
                'rank' => $rank->getPageRank(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://google.com'),
                'id'   => 'pagerank'
            ),
            Piwik::translate('SEO_Google_IndexedPages') => array(
                'rank' => $rank->getIndexedPagesGoogle(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://google.com'),
                'id'   => 'google-index',
            ),
            Piwik::translate('SEO_Bing_IndexedPages')   => array(
                'rank' => $rank->getIndexedPagesBing(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://bing.com'),
                'id'   => 'bing-index',
            ),
            Piwik::translate('SEO_AlexaRank')           => array(
                'rank' => $rank->getAlexaRank(),
                'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://alexa.com'),
                'id'   => 'alexa',
            ),
            Piwik::translate('SEO_DomainAge')           => array(
                'rank' => $rank->getAge(),
                'logo' => 'plugins/SEO/images/whois.png',
                'id'   => 'domain-age',
            ),
            Piwik::translate('SEO_ExternalBacklinks')   => array(
                'rank'         => $rank->getExternalBacklinkCount(),
                'logo'         => 'plugins/SEO/images/majesticseo.png',
                'logo_link'    => $linkToMajestic,
                'logo_tooltip' => Piwik::translate('SEO_ViewBacklinksOnMajesticSEO'),
                'id'           => 'external-backlinks',
            ),
            Piwik::translate('SEO_ReferrerDomains')     => array(
                'rank'         => $rank->getReferrerDomainCount(),
                'logo'         => 'plugins/SEO/images/majesticseo.png',
                'logo_link'    => $linkToMajestic,
                'logo_tooltip' => Piwik::translate('SEO_ViewBacklinksOnMajesticSEO'),
                'id'           => 'referrer-domains',
            ),
        );

        // Add DMOZ only if > 0 entries found
        $dmozRank = array(
            'rank' => $rank->getDmoz(),
            'logo' => \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://dmoz.org'),
            'id'   => 'dmoz',
        );
        if ($dmozRank['rank'] > 0) {
            $data[Piwik::translate('SEO_Dmoz')] = $dmozRank;
        }

        return DataTable::makeFromIndexedArray($data);
    }
}

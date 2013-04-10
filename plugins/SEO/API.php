<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SEO
 */

/**
 * @see plugins/Referers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referers/functions.php';

/**
 * The SEO API lets you access a list of SEO metrics for the specified URL: Google Pagerank, Goolge/Bing indexed pages
 * Alexa Rank, age of the Domain name and count of DMOZ entries.
 *
 * @package Piwik_SEO
 */
class Piwik_SEO_API
{
    static private $instance = null;

    /**
     * @return Piwik_SEO_API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Returns SEO statistics for a URL.
     *
     * @param string $url URL to request SEO stats for
     * @return Piwik_DataTable
     */
    public function getRank($url)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rank = new Piwik_SEO_RankChecker($url);

        $linkToMajestic = Piwik_SEO_MajesticClient::getLinkForUrl($url);

        $data = array(
            'Google PageRank'                          => array(
                'rank' => $rank->getPageRank(),
                'logo' => Piwik_getSearchEngineLogoFromUrl('http://google.com'),
                'id'   => 'pagerank'
            ),
            Piwik_Translate('SEO_Google_IndexedPages') => array(
                'rank' => $rank->getIndexedPagesGoogle(),
                'logo' => Piwik_getSearchEngineLogoFromUrl('http://google.com'),
                'id'   => 'google-index',
            ),
            Piwik_Translate('SEO_Bing_IndexedPages')   => array(
                'rank' => $rank->getIndexedPagesBing(),
                'logo' => Piwik_getSearchEngineLogoFromUrl('http://bing.com'),
                'id'   => 'bing-index',
            ),
            Piwik_Translate('SEO_AlexaRank')           => array(
                'rank' => $rank->getAlexaRank(),
                'logo' => Piwik_getSearchEngineLogoFromUrl('http://alexa.com'),
                'id'   => 'alexa',
            ),
            Piwik_Translate('SEO_DomainAge')           => array(
                'rank' => $rank->getAge(),
                'logo' => 'plugins/SEO/images/whois.png',
                'id'   => 'domain-age',
            ),
            Piwik_Translate('SEO_ExternalBacklinks')   => array(
                'rank'         => $rank->getExternalBacklinkCount(),
                'logo'         => 'plugins/SEO/images/majesticseo.png',
                'logo_link'    => $linkToMajestic,
                'logo_tooltip' => Piwik_Translate('SEO_ViewBacklinksOnMajesticSEO'),
                'id'           => 'external-backlinks',
            ),
            Piwik_Translate('SEO_ReferrerDomains')     => array(
                'rank'         => $rank->getReferrerDomainCount(),
                'logo'         => 'plugins/SEO/images/majesticseo.png',
                'logo_link'    => $linkToMajestic,
                'logo_tooltip' => Piwik_Translate('SEO_ViewBacklinksOnMajesticSEO'),
                'id'           => 'referrer-domains',
            ),
        );

        // Add DMOZ only if > 0 entries found
        $dmozRank = array(
            'rank' => $rank->getDmoz(),
            'logo' => Piwik_getSearchEngineLogoFromUrl('http://dmoz.org'),
            'id'   => 'dmoz',
        );
        if ($dmozRank['rank'] > 0) {
            $data[Piwik_Translate('SEO_Dmoz')] = $dmozRank;
        }

        $dataTable = new Piwik_DataTable();
        $dataTable->addRowsFromArrayWithIndexLabel($data);
        return $dataTable;
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Url;
use Piwik\UrlHelper;

require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function getReferrerType()
    {
        try {
            $referrerType = getReferrerTypeFromShortName($this->details['referer_type']);
        } catch (\Exception $e) {
            $referrerType = '';
        }

        return $referrerType;
    }

    public function getReferrerTypeName()
    {
        return getReferrerTypeLabel($this->details['referer_type']);
    }

    public function getKeyword()
    {
        $keyword = $this->details['referer_keyword'];
        
        if ($this->getReferrerType() == 'search') {
            $keyword = API::getCleanKeyword($keyword);
        }

        return urldecode($keyword);
    }

    public function getReferrerUrl()
    {
        if ($this->getReferrerType() == 'search') {
            if ($this->details['referer_keyword'] == API::LABEL_KEYWORD_NOT_DEFINED) {

                return 'http://piwik.org/faq/general/#faq_144';

            } // Case URL is google.XX/url.... then we rewrite to the search result page url
            elseif ($this->getReferrerName() == 'Google'
                && strpos($this->details['referer_url'], '/url')
            ) {
                $refUrl = @parse_url($this->details['referer_url']);
                if (isset($refUrl['host'])) {
                    $url = getSearchEngineUrlFromUrlAndKeyword('http://google.com', $this->getKeyword());
                    $url = str_replace('google.com', $refUrl['host'], $url);

                    return $url;
                }
            }
        }

        if (UrlHelper::isLookLikeUrl($this->details['referer_url'])) {
            return $this->details['referer_url'];
        }

        return null;
    }

    public function getKeywordPosition()
    {
        if ($this->getReferrerType() == 'search'
            && strpos($this->getReferrerName(), 'Google') !== false
        ) {
            $url = @parse_url($this->details['referer_url']);
            if (empty($url['query'])) {

                return null;
            }

            $position = UrlHelper::getParameterFromQueryString($url['query'], 'cd');
            if (!empty($position)) {

                return $position;
            }
        }

        return null;
    }

    public function getReferrerName()
    {
        return urldecode($this->details['referer_name']);
    }

    public function getSearchEngineUrl()
    {
        if ($this->getReferrerType() == 'search'
            && !empty($this->details['referer_name'])
        ) {

            return getSearchEngineUrlFromName($this->details['referer_name']);
        }

        return null;
    }

    public function getSearchEngineIcon()
    {
        $searchEngineUrl = $this->getSearchEngineUrl();

        if (!is_null($searchEngineUrl)) {

            return getSearchEngineLogoFromUrl($searchEngineUrl);
        }

        return null;
    }

}
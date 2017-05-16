<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\UrlHelper;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['referrerType']             = $this->getReferrerType();
        $visitor['referrerTypeName']         = $this->getReferrerTypeName();
        $visitor['referrerName']             = $this->getReferrerName();
        $visitor['referrerKeyword']          = $this->getKeyword();
        $visitor['referrerKeywordPosition']  = $this->getKeywordPosition();
        $visitor['referrerUrl']              = $this->getReferrerUrl();
        $visitor['referrerSearchEngineUrl']  = $this->getSearchEngineUrl();
        $visitor['referrerSearchEngineIcon'] = $this->getSearchEngineIcon();
    }

    public function renderVisitorDetails($visitorDetails)
    {
        $view            = new View('@Referrers/_visitorDetails.twig');
        $view->visitInfo = $visitorDetails;
        return $view->render();
    }

    protected function getReferrerType()
    {
        try {
            $referrerType = getReferrerTypeFromShortName($this->details['referer_type']);
        } catch (\Exception $e) {
            $referrerType = '';
        }

        return $referrerType;
    }

    protected function getReferrerTypeName()
    {
        return getReferrerTypeLabel($this->details['referer_type']);
    }

    protected function getKeyword()
    {
        $keyword = $this->details['referer_keyword'];

        if ($this->getReferrerType() == 'search') {
            $keyword = API::getCleanKeyword($keyword);
        }

        return urldecode($keyword);
    }

    protected function getReferrerUrl()
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
                    $url = SearchEngine::getInstance()->getBackLinkFromUrlAndKeyword('http://google.com',
                        $this->getKeyword());
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

    protected function getKeywordPosition()
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

    protected function getReferrerName()
    {
        return urldecode($this->details['referer_name']);
    }

    protected function getSearchEngineUrl()
    {
        if ($this->getReferrerType() == 'search'
            && !empty($this->details['referer_name'])
        ) {

            return SearchEngine::getInstance()->getUrlFromName($this->details['referer_name']);
        }

        return null;
    }

    protected function getSearchEngineIcon()
    {
        $searchEngineUrl = $this->getSearchEngineUrl();

        if (!is_null($searchEngineUrl)) {

            return SearchEngine::getInstance()->getLogoFromUrl($searchEngineUrl);
        }

        return null;
    }
}
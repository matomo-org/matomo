<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Common;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\UrlHelper;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['referrerType']              = $this->getReferrerType();
        $visitor['referrerTypeName']          = $this->getReferrerTypeName();
        $visitor['referrerName']              = $this->getReferrerName();
        $visitor['referrerKeyword']           = $this->getKeyword();
        $visitor['referrerKeywordPosition']   = $this->getKeywordPosition();
        $visitor['referrerUrl']               = $this->getReferrerUrl();
        $visitor['referrerSearchEngineUrl']   = $this->getSearchEngineUrl();
        $visitor['referrerSearchEngineIcon']  = $this->getSearchEngineIcon();
        $visitor['referrerSocialNetworkUrl']  = $this->getSocialNetworkUrl();
        $visitor['referrerSocialNetworkIcon'] = $this->getSocialNetworkIcon();
    }

    public function renderVisitorDetails($visitorDetails)
    {
        $view            = new View('@Referrers/_visitorDetails.twig');
        $view->sendHeadersWhenRendering = false;
        $view->visitInfo = $visitorDetails;
        return [[ 10, $view->render() ]];
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
        $keyword = Common::unsanitizeInputValue($this->details['referer_keyword']);

        if ($this->getReferrerType() == 'search') {
            $keyword = API::getCleanKeyword($keyword);
        }

        return urldecode($keyword);
    }

    protected function getReferrerUrl()
    {
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

    protected function getReferrerName(): string
    {
         return html_entity_decode(($this->details['referer_name'] ?? ''), ENT_QUOTES, "UTF-8");
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

    protected function getSocialNetworkUrl()
    {
        if ($this->getReferrerType() == 'social'
            && !empty($this->details['referer_name'])
        ) {

            return Social::getInstance()->getMainUrl($this->details['referer_url']);
        }

        return null;
    }

    protected function getSocialNetworkIcon()
    {
        $socialNetworkUrl = $this->getSocialNetworkUrl();

        if (!is_null($socialNetworkUrl)) {

            return Social::getInstance()->getLogoFromUrl($socialNetworkUrl);
        }

        return null;
    }
}
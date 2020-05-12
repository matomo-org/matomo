<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\ProfileSummary;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Live;
use Piwik\Plugins\Live\ProfileSummary\ProfileSummaryAbstract;
use Piwik\Url;
use Piwik\View;

/**
 * Class LocationSummary
 */
class LocationSummary extends ProfileSummaryAbstract
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Piwik::translate('UserCountry_Location');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (empty($this->profile['countries'])) {
            return '';
        }

        $view              = new View('@UserCountry/_profileSummary.twig');
        $view->visitorData = $this->profile;

        if (Common::getRequestVar('showMap', 1) == 1
            && !empty($view->visitorData['hasLatLong'])
            && \Piwik\Plugin\Manager::getInstance()->isPluginLoaded('UserCountryMap')
        ) {
            $view->userCountryMapUrl = $this->getUserCountryMapUrlForVisitorProfile();
        }

        return $view->render();
    }

    private function getUserCountryMapUrlForVisitorProfile()
    {
        $params = array(
            'module'             => 'UserCountryMap',
            'action'             => 'realtimeMap',
            'segment'            => Live\Live::getSegmentWithVisitorId(),
            'visitorId'          => false,
            'changeVisitAlpha'   => 0,
            'removeOldVisits'    => 0,
            'realtimeWindow'     => 'false',
            'showFooterMessage'  => 0,
            'showDateTime'       => 0,
            'doNotRefreshVisits' => 1
        );
        return Url::getCurrentQueryStringWithParametersModified($params);
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 100;
    }
}
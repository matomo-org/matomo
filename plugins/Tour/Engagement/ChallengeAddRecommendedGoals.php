<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour\Engagement;

use Piwik\API\Request as ApiRequest;
use Piwik\Piwik;
use Piwik\Request;
use Piwik\Url;

class ChallengeAddRecommendedGoals extends Challenge
{
    public const REQUEST_PARAMETER = 'createdFromRecommendedGoal';

    public function getName()
    {
        return Piwik::translate('Tour_AddRecommendedGoals');
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_AddRecommendedGoalsDescription');
    }

    public function getId()
    {
        return 'add_recommended_goals';
    }

    public function getUrl()
    {
        $request = Request::fromRequest();
        $idSite = $request->getIntegerParameter('idSite', 1);
        $period = urlencode($request->getStringParameter('period', 'day'));
        $date = urlencode($request->getStringParameter('date', 'yesterday'));

        $reportingPageParameters = array(
            'module' => 'CoreHome',
            'action' => 'index',
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
        );
        $reportingPageHashParameters = array(
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'category' => 'Goals_Goals',
            'subcategory' => $this->getGoalsSubcategory($idSite),
        );

        return 'index.php?' . Url::getQueryStringFromParameters($reportingPageParameters)
            . '#?' . Url::getQueryStringFromParameters($reportingPageHashParameters);
    }

    private function getGoalsSubcategory(int $idSite): string
    {
        $goals = ApiRequest::processRequest(
            'Goals.getGoals',
            array('idSite' => $idSite, 'filter_limit' => '-1'),
            $default = array()
        );

        if (count($goals)) {
            return 'Goals_ManageGoals';
        }

        return 'Goals_AddNewGoal';
    }
}

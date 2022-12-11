<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreAdminHome\Controller;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\UserCountry\UserCountry;
use Piwik\Plugins\UsersManager\UsersManager;

class Challenges
{
    /**
     * @var DataFinder
     */
    private $finder;

    public function __construct(DataFinder $dataFinder)
    {
        $this->finder = $dataFinder;
    }

    private function isActivePlugin($pluginName)
    {
        return Plugin\Manager::getInstance()->isPluginActivated($pluginName);
    }

    public function getChallenges()
    {
        /** @var Challenge[] $challenges */
        $challenges = array(
           StaticContainer::get(ChallengeTrackingCode::class),
        );

        $challenges[] = StaticContainer::get(ChallengeSetupConsentManager::class);

        if ($this->isActivePlugin('Goals')) {
            $challenges[] = StaticContainer::get(ChallengeCreatedGoal::class);
        }

        $challenges[] = StaticContainer::get(ChallengeCustomLogo::class);

        if ($this->isActivePlugin('UsersManager') && UsersManager::isUsersAdminEnabled()) {
            $challenges[] = StaticContainer::get(ChallengeAddedUser::class);
        }
        if ($this->isActivePlugin('SitesManager') && SitesManager::isSitesAdminEnabled()) {
            $challenges[] = StaticContainer::get(ChallengeAddedWebsite::class);
        }

        $challenges[] = StaticContainer::get(ChallengeFlattenActions::class);
        $challenges[] = StaticContainer::get(ChallengeChangeVisualisation::class);

        if ($this->isActivePlugin('ScheduledReports')) {
            $challenges[] = StaticContainer::get(ChallengeScheduledReport::class);
        }
        if ($this->isActivePlugin('Dashboard')) {
            $challenges[] = StaticContainer::get(ChallengeCustomiseDashboard::class);
        }
        if ($this->isActivePlugin('SegmentEditor')) {
            $challenges[] = StaticContainer::get(ChallengeAddedSegment::class);
        }
        if ($this->isActivePlugin('Annotations')) {
            $challenges[] = StaticContainer::get(ChallengeAddedAnnotation::class);
        }

        if ($this->isActivePlugin('TwoFactorAuth')) {
            $challenges[] = StaticContainer::get(ChallengeSetupTwoFa::class);
        }

        if (Controller::isGeneralSettingsAdminEnabled()) {
            $challenges[] = StaticContainer::get(ChallengeDisableBrowserArchiving::class);
        }

        if (UserCountry::isGeoLocationAdminEnabled()) {
            $challenges[] = StaticContainer::get(ChallengeConfigureGeolocation::class);
        }

        // we're adding this simple challenge only later in the process since there might not be enough data yet in
        // the beginning to actually get much value from it
        $challenges[] = StaticContainer::get(ChallengeSelectDateRange::class);

        if ($this->isActivePlugin('Live')) {
            $challenges[] = StaticContainer::get(ChallengeViewVisitsLog::class);
            $challenges[] = StaticContainer::get(ChallengeViewVisitorProfile::class);
        }

        $challenges[] = StaticContainer::get(ChallengeViewRowEvolution::class);

        if ($this->isActivePlugin('Marketplace')) {
            $challenges[] = StaticContainer::get(ChallengeBrowseMarketplace::class);
        }

        /**
         * Triggered to add new challenges to the "welcome to Matomo tour".
         *
         * **Example**
         *
         *     public function addChallenge(&$challenges)
         *     {
         *         $challenges[] = new MyChallenge();
         *     }
         *
         * @param Challenge[] $challenges An array of challenges
         */
        Piwik::postEvent('Tour.filterChallenges', array(&$challenges));

        return $challenges;
    }


}
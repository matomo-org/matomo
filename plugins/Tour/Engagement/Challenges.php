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
use Piwik\Plugins\CoreAdminHome\Controller;
use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\UserCountry\UserCountry;

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

    public function getChallenges()
    {
        /** @var Challenge[] $challenges */
        $challenges = array(
            StaticContainer::get(ChallengeTrackingCode::class),
            StaticContainer::get(ChallengeCreatedGoal::class),
            StaticContainer::get(ChallengeCustomLogo::class),
            StaticContainer::get(ChallengeAddedUser::class),
            StaticContainer::get(ChallengeAddedWebsite::class),
            StaticContainer::get(ChallengeScheduledReport::class),
            StaticContainer::get(ChallengeCustomiseDashboard::class),
            StaticContainer::get(ChallengeAddedSegment::class),
            StaticContainer::get(ChallengeAddedAnnotation::class),
        );

        if (Controller::isGeneralSettingsAdminEnabled()) {
            $challenges[] = StaticContainer::get(ChallengeDisableBrowserArchiving::class);
        }

        if (UserCountry::isGeoLocationAdminEnabled()) {
            $challenges[] = StaticContainer::get(ChallengeConfigureGeolocation::class);
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
<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour;

use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SiteContentDetection\ConsentManagerDetectionAbstract;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\SiteContentDetector;
use Piwik\Plugins\Tour\Engagement\Levels;
use Piwik\Plugins\Tour\Engagement\Challenges;

/**
 * API for Tour plugin which helps you getting familiar with Matomo.
 *
 * @method static \Piwik\Plugins\Tour\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * @var Challenges
     */
    private $challenges;

    /**
     * Levels
     */
    private $levels;

    /** @var SiteContentDetector */
    private $siteContentDetector;

    public function __construct(Challenges $challenges, Levels $levels, SiteContentDetector $siteContentDetector)
    {
        $this->challenges = $challenges;
        $this->levels = $levels;
        $this->siteContentDetector = $siteContentDetector;
    }

    /**
     * Get all challenges that can be completed by a super user.
     *
     * @return array[]
     */
    public function getChallenges()
    {
        Piwik::checkUserHasSuperUserAccess();

        $challenges = array();

        $login = Piwik::getCurrentUserLogin();

        foreach ($this->challenges->getChallenges() as $challenge) {

            if ($challenge->isDisabled()) {
                continue;
            }

            $challenges[] = [
                'id' => $challenge->getId(),
                'name' => $challenge->getName(),
                'description' => $challenge->getDescription(),
                'isCompleted' => $challenge->isCompleted($login),
                'isSkipped' => $challenge->isSkipped($login),
                'url' => $challenge->getUrl()
            ];
        }

        return $challenges;
    }

    /**
     * Detect consent manager details for a site
     *
     * @return null|array[]
     * @internal
     */
    public function detectConsentManager($idSite, $timeOut = 60)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $this->siteContentDetector->detectContent([SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER]);
        $consentManagers = $this->siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER);
        if (!empty($consentManagers)) {
            /** @var ConsentManagerDetectionAbstract $consentManager */
            $consentManager = $this->siteContentDetector->getSiteContentDetectionById(reset($consentManagers));
            return ['name' => $consentManager::getName(),
                    'url' => $consentManager::getInstructionUrl(),
                    'isConnected' => in_array($consentManager::getId(), $this->siteContentDetector->connectedConsentManagers)
                ];
        }

        return null;
    }

    /**
     * Skip a specific challenge.
     *
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function skipChallenge($id)
    {
        Piwik::checkUserHasSuperUserAccess();

        $login = Piwik::getCurrentUserLogin();

        foreach ($this->challenges->getChallenges() as $challenge) {
            if ($challenge->getId() === $id) {
                if (!$challenge->isCompleted($login)) {
                    $challenge->skipChallenge($login);
                    return true;
                }

                throw new \Exception('Challenge already completed');
            }
        }

        throw new \Exception('Challenge not found');
    }

    /**
     * Get details about the current level this user has progressed to.
     * @return array
     */
    public function getLevel()
    {
        Piwik::checkUserHasSuperUserAccess();

        return array(
            'description' => $this->levels->getCurrentDescription(),
            'currentLevel' => $this->levels->getCurrentLevel(),
            'currentLevelName' => $this->levels->getCurrentLevelName(),
            'nextLevelName' => $this->levels->getNextLevelName(),
            'numLevelsTotal' => $this->levels->getNumLevels(),
            'challengesNeededForNextLevel' => $this->levels->getNumChallengesNeededToNextLevel(),
        );
    }
}

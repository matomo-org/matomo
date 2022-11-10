<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour;

use Piwik\Piwik;
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

        foreach ($this->challenges->getChallenges() as $challenge) {

            if ($challenge->isDisabled()) {
                continue;
            }

            $challenges[] = [
                'id' => $challenge->getId(),
                'name' => $challenge->getName(),
                'description' => $challenge->getDescription(),
                'isCompleted' => $challenge->isCompleted(),
                'isSkipped' => $challenge->isSkipped(),
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

        $this->siteContentDetector->detectContent([SiteContentDetector::CONSENT_MANAGER]);
        if ($this->siteContentDetector->consentManagerId) {
            return ['name' => $this->siteContentDetector->consentManagerName,
                    'url' => $this->siteContentDetector->consentManagerUrl,
                    'isConnected' => $this->siteContentDetector->isConnected
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

        foreach ($this->challenges->getChallenges() as $challenge) {
            if ($challenge->getId() === $id) {
                if (!$challenge->isCompleted()) {
                    $challenge->skipChallenge();
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

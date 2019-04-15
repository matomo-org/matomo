<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Tour\Engagement\Levels;
use Piwik\Plugins\Tour\Engagement\Challenges;

/**
 * API for plugin Tour
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

    public function __construct(Challenges $challenges, Levels $levels)
    {
        $this->challenges = $challenges;
        $this->levels = $levels;
    }

    /**
     * Get all challenges
     * @return array[]
     */
    public function getChallenges()
    {
        Piwik::checkUserHasSuperUserAccess();

        $challenges = array();

        foreach ($this->challenges->getChallenges() as $challenge) {
            $challenges[] = array(
                'id' => $challenge->getId(),
                'name' => $challenge->getName(),
                'description' => $challenge->getDescription(),
                'isCompleted' => $challenge->isCompleted(),
                'isSkipped' => $challenge->isSkipped(),
                'url' => $challenge->getUrl()
            );
        }

        return $challenges;
    }

    /**
     * Skip a challenge
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function skipChallenge($id)
    {
        Piwik::checkUserHasSuperUserAccess();

        foreach ($this->challenges->getChallenges() as $challenge) {
            if ($challenge->getId() === $id) {
                $challenge->skipChallenge();
                return true;
            }
        }

        throw new \Exception('Challenge not found');
    }

    /**
     * Get current challenge level
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

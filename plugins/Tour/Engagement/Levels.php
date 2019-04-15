<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;

class Levels
{
    /**
     * @var Challenges
     */
    private $challenges;

    /**
     * GetEngagement constructor.
     * @param Challenges $challenges
     */
    public function __construct(Challenges $challenges)
    {
        $this->challenges = $challenges;
    }

    public function getNumChallengesCompleted()
    {
        $challenges = $this->challenges->getChallenges();

        $completed = 0;
        foreach ($challenges as $challenge) {
            if ($challenge->isSkipped() || $challenge->isCompleted()) {
                $completed++;
            }
        }
        return $completed;
    }

    public function getCurrentLevel()
    {
        $completed = $this->getNumChallengesCompleted();

        $current = '';
        foreach ($this->getLevels() as $threshold => $level) {
            if ($completed >= $threshold) {
                $current = $level;
            }
        }
        return $current;
    }

    public function getNextLevel()
    {
        $completed = $this->getNumChallengesCompleted();

        foreach ($this->getLevels() as $threshold => $level) {
            if ($completed < $threshold) {
               return $level;
            }
        }
    }

    public function getNumChallengesNeededToNextLevel()
    {
        $completed = $this->getNumChallengesCompleted();

        foreach ($this->getLevels() as $threshold => $level) {
            if ($completed < $threshold) {
                return $threshold - $completed;
            }
        }
    }

    public function getCurrentDescription()
    {
        $numChallengesCompleted = $this->getNumChallengesCompleted();
        if ($numChallengesCompleted <= 5) {
            return Piwik::translate('Tour_Part1Title');
        }

        if ($numChallengesCompleted <= 10) {
            return Piwik::translate('Tour_Part2Title');
        }

        if ($numChallengesCompleted <= 14) {
            return Piwik::translate('Tour_Part3Title');
        }

        return Piwik::translate('Tour_Part4Title');
    }

    public function getLevels()
    {
       return array(
            0 => 'Matomo Beginner',
            5 => 'Matomo Intermediate',
            10 => 'Matomo Professional',
            15 => 'Matomo Expert',
            20 => 'Matomo Senior Expert'
       );
    }


}
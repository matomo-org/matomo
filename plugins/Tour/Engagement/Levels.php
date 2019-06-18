<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\API\Request;
use Piwik\Piwik;

class Levels
{
    /**
     * @var array
     */
    private $challenges = array();

    private function getChallenges()
    {
        if (empty($this->challenges)) {
            $this->challenges = Request::processRequest('Tour.getChallenges', [], []);
        }

        return $this->challenges;
    }

    public function getNumChallengesCompleted()
    {
        $challenges = $this->getChallenges();

        $completed = 0;
        foreach ($challenges as $challenge) {
            if ($challenge['isSkipped'] || $challenge['isCompleted']) {
                $completed++;
            }
        }
        return $completed;
    }

    public function getCurrentLevel()
    {
        $completed = $this->getNumChallengesCompleted();

        $current = 0;
        foreach ($this->getLevels() as $threshold => $level) {
            if ($completed >= $threshold) {
                $current++;
            }
        }
        return $current;
    }

    public function getCurrentLevelName()
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

    public function getNextLevelName()
    {
        $completed = $this->getNumChallengesCompleted();

        foreach ($this->getLevels() as $threshold => $level) {
            if ($completed < $threshold) {
               return $level;
            }
        }
    }

    public function getNumLevels()
    {
        $levels = $this->getLevels();
        return count($levels);
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
        $login = Piwik::getCurrentUserLogin();
        $numChallengesCompleted = $this->getNumChallengesCompleted();
        $numChallengesTotal = $this->getNumChallengesTotal();

        if ($numChallengesCompleted <= ($numChallengesTotal / 4)) {
            return Piwik::translate('Tour_Part1Title', $login);
        }

        if ($numChallengesCompleted <= ($numChallengesTotal / 2)) {
            return Piwik::translate('Tour_Part2Title', $login);
        }

        if ($numChallengesCompleted <= ($numChallengesTotal / 1.333)) {
            return Piwik::translate('Tour_Part3Title', $login);
        }

        return Piwik::translate('Tour_Part4Title', $login);
    }

    private function getNumChallengesTotal()
    {
        $challenges = $this->getChallenges();
        return count($challenges);
    }

    public function getLevels()
    {
        $numChallengesTotal = $this->getNumChallengesTotal();

        $levels = array(
            0 => Piwik::translate('Tour_MatomoBeginner'),
            5 => Piwik::translate('Tour_MatomoIntermediate'),
        );

        if ($numChallengesTotal > 10) {
            // the number of challenges varies from Matomo to Matomo depending on activated plugins and activated
            // features. Therefore we may remove some levels if there aren't too many challenges available.
            $levels[10] = Piwik::translate('Tour_MatomoTalent');
        }

        if ($numChallengesTotal > 15) {
            $levels[15] = Piwik::translate('Tour_MatomoProfessional');
        }

        $levels[$numChallengesTotal] = Piwik::translate('Tour_MatomoExpert');

        return $levels;
    }


}
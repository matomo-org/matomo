<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour;

use Piwik\Piwik;
use Piwik\Plugins\Tour\Engagement\Levels;
use Piwik\Plugins\Tour\Engagement\Challenges;

/**
 * Provides API methods for Tour challenges and engagement levels.
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
     * @var Levels
     */
    private $levels;

    public function __construct(Challenges $challenges, Levels $levels)
    {
        $this->challenges = $challenges;
        $this->levels = $levels;
    }

    /**
     * Returns the available Tour challenges for the current super user.
     *
     * @return list<array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     isCompleted: bool,
     *     isSkipped: bool,
     *     url: string
     * }> Tour challenge metadata including completion and skip state.
     */
    public function getChallenges(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $challenges = [];

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
                'url' => $challenge->getUrl(),
            ];
        }

        return $challenges;
    }

    /**
     * Marks the specified Tour challenge as skipped for the current super user.
     *
     * @param string $id The challenge ID to skip.
     * @return true Returns `true` when the challenge was skipped successfully.
     */
    public function skipChallenge(string $id): bool
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
     * Returns the current Tour level details for the current super user.
     *
     * @return array{
     *     description: string,
     *     currentLevel: int,
     *     currentLevelName: string,
     *     nextLevelName: string|null,
     *     numLevelsTotal: int,
     *     challengesNeededForNextLevel: int
     * } Tour level details including the current and next level names.
     */
    public function getLevel(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        return [
            'description' => $this->levels->getCurrentDescription(),
            'currentLevel' => $this->levels->getCurrentLevel(),
            'currentLevelName' => $this->levels->getCurrentLevelName(),
            'nextLevelName' => $this->levels->getNextLevelName(),
            'numLevelsTotal' => $this->levels->getNumLevels(),
            'challengesNeededForNextLevel' => $this->levels->getNumChallengesNeededToNextLevel(),
        ];
    }
}

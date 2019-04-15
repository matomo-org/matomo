<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Widgets;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Plugins\Tour\Engagement\Levels;
use Piwik\Plugins\Tour\Engagement\Challenges;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Piwik;

class GetEngagement extends Widget
{
    const NUM_CHALLENGES_PER_PAGE = 5;

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

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('Become a Matomo Expert');
        $config->setOrder(99);

        if (!Piwik::hasUserSuperUserAccess()) {
            $config->disable();
        }
    }

    public function render()
    {
        Piwik::checkUserHasSuperUserAccess();

        $numCompletedWithoutInterruption = 0;

        $challenges = Request::processRequest('Tour.getChallenges');
        $level = Request::processRequest('Tour.getLevel');

        $done = true;
        foreach ($challenges as $challenge) {
            if (!$challenge['isCompleted'] && !$challenge['isSkipped']) {
                $done = false;
            } else if ($done) {
                // as soon as some challenge was not completed, we need to make sure to show that page.
                $numCompletedWithoutInterruption++;
            }
        }

        $page = floor($numCompletedWithoutInterruption / self::NUM_CHALLENGES_PER_PAGE);
        $page = Common::getRequestVar('page', $page, 'int');
        $numPagesTotal = floor(count($challenges) / self::NUM_CHALLENGES_PER_PAGE); // floor cause zero indexed

        $startPosition = self::NUM_CHALLENGES_PER_PAGE * $page;
        $challenges = array_slice($challenges, $startPosition, self::NUM_CHALLENGES_PER_PAGE);

        $params = array(
            'isCompleted' => $done,
            'challenges' => $challenges,
            'currentPage' => $page,
            'previousPage' => $page >= 1 ? $page - 1 : null,
            'nextPage' => $page < $numPagesTotal ? $page + 1 : null,
        );
        $params = array_merge($params, $level);

        return $this->renderTemplate('engagement', $params);
    }

}
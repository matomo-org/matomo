<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Widgets;

use Piwik\Plugins\Tour\Engagement\Parts;
use Piwik\Plugins\Tour\Engagement\Steps;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Piwik;

class GetEngagement extends Widget
{
    /**
     * @var Steps
     */
    private $steps;

    /**
     * GetEngagement constructor.
     * @param Steps $steps
     */
    public function __construct(Steps $steps)
    {
        $this->steps = $steps;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('Getting Started');
        $config->setOrder(99);

        if (!Piwik::hasUserSuperUserAccess()) {
            $config->disable();
        }
    }

    public function render()
    {
        $numCompletedWithoutInterruption = 0;

        $steps = $this->steps->getSteps();

        $done = true;
        foreach ($steps as $step) {
            if (!$step['done'] && !$step['skipped']) {
                $done = false;
            } else if ($done) {
                // as soon as some step was not completed, we need to make sure to show that page.
                $numCompletedWithoutInterruption++;
            }
        }

        if ($done) {
            return '<p class="widgetBody tourEngagement"><strong class="completed">' . Piwik::translate('Tour_CompletionTitle') .'</strong> ' . Piwik::translate('Tour_CompletionMessage') . '<br /><br /></p>';
        }

        $numStepsToShowPerPage = 5;
        $page = floor($numCompletedWithoutInterruption / $numStepsToShowPerPage);
        $startSteps = $numStepsToShowPerPage * $page;
        $steps = array_slice($steps, $startSteps, $numStepsToShowPerPage);

        return $this->renderTemplate('engagement', array(
            'steps' => $steps,
            'description' => Piwik::translate('Tour_Part1Title')
        ));
    }

}
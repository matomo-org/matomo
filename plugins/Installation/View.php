<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

/**
 *
 */
class View extends \Piwik\View
{
    public function __construct($subtemplatePath, $installationSteps, $currentStepName)
    {
        parent::__construct($subtemplatePath);

        $this->steps = array_keys($installationSteps);
        $this->allStepsTitle = array_values($installationSteps);
        $this->currentStepName = $currentStepName;
        $this->showNextStep = false;
    }

    public function render()
    {
        // prepare the all steps templates
        $this->currentStepId = array_search($this->currentStepName, $this->steps);
        $this->totalNumberOfSteps = count($this->steps);

        $this->percentDone = round(($this->currentStepId) * 100 / ($this->totalNumberOfSteps - 1));
        $this->percentToDo = 100 - $this->percentDone;

        $this->nextModuleName = '';
        if (isset($this->steps[$this->currentStepId + 1])) {
            $this->nextModuleName = $this->steps[$this->currentStepId + 1];
        }
        $this->previousModuleName = '';
        if (isset($this->steps[$this->currentStepId - 1])) {
            $this->previousModuleName = $this->steps[$this->currentStepId - 1];
        }
        $this->previousPreviousModuleName = '';
        if (isset($this->steps[$this->currentStepId - 2])) {
            $this->previousPreviousModuleName = $this->steps[$this->currentStepId - 2];
        }

        return parent::render();
    }
}

<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

class Piwik_Install_View extends Piwik_View
{
	protected $mainTemplate = 'Installation/templates/structure.tpl';
	function __construct($subtemplatePath, $installationSteps, $currentStepName)
	{
		parent::__construct($this->mainTemplate);
		$this->subTemplateToLoad = $subtemplatePath;
		$this->steps = $installationSteps;
		$this->currentStepName = $currentStepName;
		$this->showNextStep = false;
	}
	
	function render()
	{
		// prepare the all steps templates
		$this->allStepsTitle = $this->steps;
		$this->currentStepId = array_search($this->currentStepName, $this->steps);
		$this->totalNumberOfSteps = count($this->steps);
		
		$this->percentDone = round(($this->currentStepId) * 100 / ($this->totalNumberOfSteps-1));
		$this->percentToDo = 100 - $this->percentDone;
		
		$this->nextModuleName = '';
		if(isset($this->steps[$this->currentStepId + 1]))
		{
			$this->nextModuleName = $this->steps[$this->currentStepId + 1];
		}
		
		return parent::render();
		
	}
}
<ul>
{foreach from=$allStepsTitle key=stepId item=stepName}
	{if $currentStepId > $stepId}
	<li class="pastStep">{$stepName}</li>
	{elseif $currentStepId == $stepId}
	<li class="actualStep">{$stepName}</li>
	{else}
	<li class="futureStep">{$stepName}</li>
	{/if}
{/foreach}
</ul>
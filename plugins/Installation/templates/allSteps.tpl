<ul>
{foreach from=$allStepsTitle key=stepId item=stepName}
	{if $currentStepId > $stepId}
	<li class="pastStep">{$stepName|translate}</li>
	{elseif $currentStepId == $stepId}
	<li class="actualStep">{$stepName|translate}</li>
	{else}
	<li class="futureStep">{$stepName|translate}</li>
	{/if}
{/foreach}
</ul>

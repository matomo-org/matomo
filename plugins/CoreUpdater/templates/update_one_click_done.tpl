{include file="CoreUpdater/templates/header.tpl"}

{foreach from=$feedbackMessages item=message}
	<p>{$message}</p>
{/foreach}

{if $coreError}
	<br /><br />
	<div class="error"><img src="themes/default/images/error_medium.png" /> {$coreError}</div>
	<br /><br />
	<div class="warning"><img src="themes/default/images/warning_medium.png" /> {'CoreUpdater_UpdateHasBeenCancelledExplanation'|translate:"<br /><br />":"<a target='_blank' href='misc/redirectToUrl.php?url=http://piwik.org/docs/update/'>":"</a>"}</div>
	<br /><br />
{/if}

<form action="index.php">
<input type="submit" class="submit" value="{'CoreUpdater_ContinueToPiwik'|translate}" />
</form>
{include file="CoreUpdater/templates/footer.tpl"}

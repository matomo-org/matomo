{include file="CoreUpdater/templates/header.tpl"}

{foreach from=$feedbackMessages item=message}
	<p>{$message}</p>
{/foreach}

{if $coreError}
	<br><br>
	<div class="error">
		<img src="themes/default/images/error_medium.png"> {$coreError}
		<br><br>{'CoreUpdater_UpdateHasBeenCancelled'|translate}
	</div>
	<br><br>
{/if}

<form action="index.php">
<input type="submit" class="submit" value="{'CoreUpdater_ContinueToPiwik'|translate}"/>
</form>
{include file="CoreUpdater/templates/footer.tpl"}

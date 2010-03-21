{include file="CoreUpdater/templates/header.tpl"}
<span style="float:right">{postEvent name="template_topBar"}</span>
{assign var='helpMessage' value='CoreUpdater_HelpMessageContent'|translate:'<a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/faq/">':'</a>':'</li><li>'}

{if $coreError}
	<br /><br />
	<div class="error">
		<img src="themes/default/images/error_medium.png" /> {'CoreUpdater_CriticalErrorDuringTheUpgradeProcess'|translate}
		{foreach from=$errorMessages item=message}
		<pre>{$message}</pre>
		{/foreach}
	</div>
	<br />
	<p>{'CoreUpdater_HelpMessageIntroductionWhenError'|translate}
	<ul><li>{$helpMessage}</li></ul></p>
{else}
	{if $coreToUpdate || count($pluginNamesToUpdate) > 0}
		<p style='font-size:110%;padding-top:1em;'><b>{'CoreUpdater_DatabaseUpgradeRequired'|translate}</b></p>
		<p>{'CoreUpdater_YourDatabaseIsOutOfDate'|translate}</p>

		{if $coreToUpdate}
			<p>{'CoreUpdater_PiwikWillBeUpgradedFromVersionXToVersionY'|translate:$current_piwik_version:$new_piwik_version}</p>
		{/if}

		{if count($pluginNamesToUpdate) > 0}
			{assign var=listOfPlugins value=$pluginNamesToUpdate|@implode:', '}
			<p>{'CoreUpdater_TheFollowingPluginsWillBeUpgradedX'|translate:$listOfPlugins}</p>
		{/if}

		<p><strong>{'CoreUpdater_NoteForLargePiwikInstances'|translate}</strong></p>
		<ul>
		<li>{'CoreUpdater_TheUpgradeProcessMayFailExecuteCommand'|translate:$commandUpgradePiwik}</li>
		<li>{'CoreUpdater_YouCouldManuallyExecuteSqlQueries'|translate}<br />
			<a href='#' id='showSql' style='margin-left:20px'>› {'CoreUpdater_ClickHereToViewSqlQueries'|translate}</a>
		<div id='sqlQueries' style='display:none'>
		<br />
			<code>
			# {'CoreUpdater_NoteItIsExpectedThatQueriesFail'|translate}<br /><br />
			{foreach from=$queries item=query}&nbsp;&nbsp;&nbsp;{$query}<br />
			{/foreach}
			</code>
		</div>
		<br /><br />
		<p><strong>{'CoreUpdater_ReadyToGo'|translate}</strong></p>
		<p>{'CoreUpdater_TheUpgradeProcessMayTakeAWhilePleaseBePatient'|translate}</p>
	{/if}

	{if count($warningMessages) > 0}
		<p><i>{$warningMessages[0]}</i>
		{if count($warningMessages) > 1}
			<button id="more-results" class="ui-button ui-state-default ui-corner-all">{'General_Details'|translate}</button>
		{/if}
		</p>
	{/if}

	{if $coreToUpdate || count($pluginNamesToUpdate) > 0}
		<br />
		<form action="index.php">
		<input type="hidden" name="updateCorePlugins" value="1" />
		<input type="submit" class="submit" value="{'CoreUpdater_UpgradePiwik'|translate}" />
		</form>
	{else}
		{if count($warningMessages) == 0}
			<p class="success">{'CoreUpdater_PiwikHasBeenSuccessfullyUpgraded'|translate}</p>
		{/if}

		<br />
		<form action="index.php">
		<input type="submit" class="submit" value="{'CoreUpdater_ContinueToPiwik'|translate}" />
		</form>
	{/if}
{/if}

{include file="Installation/templates/integrityDetails.tpl"}

{literal}
<style>
code {
	background-color:#F0F7FF;
	border-color:#00008B;
	border-style:dashed dashed dashed solid;
	border-width:1px 1px 1px 5px;
	direction:ltr;
	display:block;
	margin:2px 2px 20px;
	padding:4px;
	text-align:left;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
 	$('#showSql').click( function () {
 		$('#sqlQueries').toggle(); 
 	});
});
</script>
{/literal}
{include file="CoreUpdater/templates/footer.tpl"}


{include file="CoreUpdater/templates/header.tpl"}

<p><b>{'CoreUpdater_DatabaseUpgradeRequired'|translate}</b></p>
<p>{'CoreUpdater_YourDatabaseIsOutOfDate'|translate}</p>

{if $coreToUpdate}
	<p>{'CoreUpdater_PiwikWillBeUpgradedFromVersionXToVersionY'|translate:$current_piwik_version:$new_piwik_version}</p>
{/if}

{if count($pluginNamesToUpdate) > 0}
	{assign var=listOfPlugins value=$pluginNamesToUpdate|@implode:', '}
	<p>{'CoreUpdater_TheFollowingPluginsWillBeUpgradedX'|translate:$listOfPlugins}</p>
{/if}

<p>{'CoreUpdater_TheUpgradeProcessMayTakeAWhilePleaseBePatient'|translate}</p>

<br>
<form action="index.php">
<input type="hidden" name="updateCorePlugins" value="1">
<input type="submit" class="submit" value="{'CoreUpdater_UpgradePiwik'|translate}"/>
</form>

{include file="CoreUpdater/templates/footer.tpl"}

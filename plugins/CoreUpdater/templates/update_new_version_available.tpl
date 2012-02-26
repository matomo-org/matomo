{include file="CoreUpdater/templates/header.tpl"}

<br/>
<p><b>{'CoreUpdater_ThereIsNewVersionAvailableForUpdate'|translate}</b></p>

{if $can_auto_update}
	<p>{'CoreUpdater_YouCanUpgradeAutomaticallyOrDownloadPackage'|translate:$piwik_new_version}</p>
{else}
	<p>{'Installation_SystemCheckAutoUpdateHelp'|translate}</p>
	<p>{'CoreUpdater_YouMustDownloadPackageOrFixPermissions'|translate:$piwik_new_version}
	{$makeWritableCommands}
	</p>
{/if}

{if $can_auto_update}
	<form action="index.php">
		<input type="hidden" name="module" value="CoreUpdater" />
		<input type="hidden" name="action" value="oneClickUpdate" />
		<input type="submit" class="submit" value="{'CoreUpdater_UpdateAutomatically'|translate}" />
{/if}
		<a style="margin-left:50px" class="submit button" href="{$piwik_latest_version_url}?cb={$piwik_new_version}">{'CoreUpdater_DownloadX'|translate:$piwik_new_version}</a><br />
{if $can_auto_update}
	</form>
{/if}
<br />
<a href='index.php'>&laquo; {'General_BackToPiwik'|translate}</a>
{include file="CoreUpdater/templates/footer.tpl"}


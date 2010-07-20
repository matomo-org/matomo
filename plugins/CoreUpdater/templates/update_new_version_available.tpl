{include file="CoreUpdater/templates/header.tpl"}

<p><b>{'CoreUpdater_ThereIsNewVersionAvailableForUpdate'|translate}</b></p>
<p>
{if $can_auto_update}
	{'CoreUpdater_YouCanUpgradeAutomaticallyOrDownloadPackage'|translate:$piwik_new_version}
{else}
	{'CoreUpdater_YouMustDownloadPackageOrFixPermissions'|translate:$piwik_new_version}
{/if}
</p>
{if $can_auto_update}
	<form action="index.php">
		<input type="hidden" name="module" value="CoreUpdater" />
		<input type="hidden" name="action" value="oneClickUpdate" />
		<input type="submit" class="submit" value="{'CoreUpdater_UpdateAutomatically'|translate}" />
{/if}
		<a style="margin-left:50px" class="submit button" href="http://piwik.org/latest.zip">{'CoreUpdater_DownloadX'|translate:$piwik_new_version}</a><br />
{if $can_auto_update}
	</form>
{/if}
<br />
<a href='index.php'>&laquo; {'General_BackToPiwik'|translate}</a>
{include file="CoreUpdater/templates/footer.tpl"}


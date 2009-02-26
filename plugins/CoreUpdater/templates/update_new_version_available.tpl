{include file="CoreUpdater/templates/header.tpl"}

<p><b>{'CoreUpdater_ThereIsNewVersionAvailableForUpdate'|translate}</b></p>
<p>{'CoreUpdater_YouCanUpgradeAutomaticallyOrDownloadPackage'|translate:$piwik_new_version}</p>
<br>
<form action="index.php">
<input type="hidden" name="module" value="CoreUpdater">
<input type="hidden" name="action" value="oneClickUpdate">
<input type="submit" class="submit" value="{'CoreUpdater_UpdateAutomatically'|translate}"/>
<a style="margin-left:50px" class="submit button" href="http://piwik.org/last.zip">{'CoreUpdater_DownloadX'|translate:$piwik_new_version}</a>
</form>

<a href='index.php'>&laquo; {'General_BackToPiwik'|translate}</a>
{include file="CoreUpdater/templates/footer.tpl"}


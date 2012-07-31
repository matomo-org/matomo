{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'VisitorGenerator_VisitorGenerator'|translate}</h2>
<p>{'VisitorGenerator_PluginDescription'|translate}</p>
<p>You can overwrite the log file that is used to generate fake visits (change {$accessLogPath}). This is a log file of requests to "piwik.php" in the format "Apache combined log".</p>
<form method="POST" action="{url module=VisitorGenerator action=generate}">
<table class="adminTable" style="width: 600px;">
<tr>
    <td><label for="idSite">{'General_ChooseWebsite'|translate}</label></td>
    <td>
		{include file="CoreHome/templates/sites_selection.tpl"
			showAllSitesItem=false showSelectedSite=true switchSiteOnSelect=false inputName=idSite}
    </td>
</tr>
<tr>
    <td><label for="daysToCompute">{'VisitorGenerator_DaysToCompute'|translate}</label></td>
    <td><input type="text" value="1" name="daysToCompute" /></td>
</tr>
</table>
{'VisitorGenerator_Warning'|translate}<br />
{'VisitorGenerator_NotReversible'|translate:'<b>':'</b>'}<br /><br />
<p><strong>This will generate approximately {$countActionsPerRun} fake actions on this site for each day</strong>.<br/>
</p>
{'VisitorGenerator_AreYouSure'|translate}<br /><br/>
<input type="checkbox" name="choice" id="choice" value="yes" /> <label for="choice">{'VisitorGenerator_ChoiceYes'|translate}</label>
<br />
<input type="hidden" value="{$token_auth}" name="token_auth" />
<input type="hidden" value="{$nonce}" name="form_nonce" />
<br/>
NOTE: It might take a few minutes to generate visits and actions, please be patient...<br/><br/>
<input type="submit" value="{'VisitorGenerator_Submit'|translate}" name="submit" class="submit" />
</form>

{include file="CoreAdminHome/templates/footer.tpl"}

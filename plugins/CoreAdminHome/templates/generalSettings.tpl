{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='UsersManager'}
{include file="CoreAdminHome/templates/menu.tpl"}
<script type="text/javascript" src="plugins/CoreAdminHome/templates/generalSettings.js"></script>

<h2>{'General_GeneralSettings'|translate}</h2>

{ajaxErrorDiv id=ajaxError}
{ajaxLoadingDiv id=ajaxLoading}
<table class="adminTable adminTableNoBorder" style='width:900px'>
<tr>
	<td style='width:400px'>{'General_AllowPiwikArchivingToTriggerBrowser'|translate}</td>
	<td style='width:150px'>
	<fieldset>
		<label><input type="radio" value="1" name="enableBrowserTriggerArchiving" {if $enableBrowserTriggerArchiving==1}checked="checked"{/if}> {'General_Yes'|translate}</label><br>
		<label><input type="radio" value="0" name="enableBrowserTriggerArchiving" {if $enableBrowserTriggerArchiving==0}checked="checked"{/if}> {'General_No'|translate}</label> 
	</fieldset>
	<td>
	{capture assign=browserArchivingHelp}
		{'General_ArchivingInlineHelp'|translate}<br/> 
		{'General_SeeTheOfficialDocumentationForMoreInformation'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>":"</a>"}
	{/capture}
	{$browserArchivingHelp|inlineHelp}	</td>
	</td>
</tr>

<tr>
	<td><label for="todayArchiveTTL">{'General_ReportsForTodayWillBeProcessedAtMostEvery'|translate}</label></td>
	<td>
		{'General_NSeconds'|translate:"<input size='3' value='$todayArchiveTimeToLive' id='todayArchiveTimeToLive'>"} 
	</td>
	<td width='450px'>
	{capture assign=archiveTodayTTLHelp}
		{'General_SmallTrafficYouCanLeaveDefault'|translate:10}<br/> 
		{'General_MediumToHighTrafficItIsRecommendedTo'|translate:1800:3600}
	{/capture}
	{$archiveTodayTTLHelp|inlineHelp}	</td>
	</td>
</tr>

</table>
<input type="submit" value="{'General_Save'|translate}" id="generalSettingsSubmit" class="submit">
<br/><br/>


{include file="CoreAdminHome/templates/footer.tpl"}
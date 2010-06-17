{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'VisitorGenerator_VisitorGenerator'|translate}</h2>
<p>{'VisitorGenerator_PluginDescription'|translate}</p>

<form method="POST" action="{url module=VisitorGenerator action=generate}">
<table class="adminTable adminTableNoBorder" style="width: 600px;">
<tr>
    <td><label for="idSite">{'VisitorGenerator_SelectWebsite'|translate}</label></td>
    <td><select name="idSite">
    {foreach from=$sitesList item=site}
        <option value="{$site.idsite}">{$site.name}</option>
    {/foreach}
    </select></td>
</tr>
<tr>
    <td><label for="minVisitors">{'VisitorGenerator_MinVisitors'|translate}</label></td>
    <td><input type="text" value="20" name="minVisitors" /></td>
</tr>
<tr>
    <td><label for="maxVisitors">{'VisitorGenerator_MaxVisitors'|translate}</label></td>
    <td><input type="text" value="100" name="maxVisitors" /></td>
</tr>
<tr>
    <td><label for="nbActions">{'VisitorGenerator_NbActions'|translate}</label></td>
    <td><input type="text" value="10" name="nbActions" /></td>
</tr>
<tr>
    <td><label for="daysToCompute">{'VisitorGenerator_DaysToCompute'|translate}</label></td>
    <td><input type="text" value="1" name="daysToCompute" /></td>
</tr>
</table>
<p>{'VisitorGenerator_Warning'|translate}<br />
{'VisitorGenerator_NotReversible'|translate:'<b>':'</b>'}<br /><br />
{'VisitorGenerator_AreYouSure'|translate}<br />
</p>
<input type="checkbox" name="choice" id="choice" value="yes" /> <label for="choice">{'VisitorGenerator_ChoiceYes'|translate}</label>
<br />
<input type="hidden" value="{$token_auth}" name="token_auth" />
<input type="submit" value="{'VisitorGenerator_Submit'|translate}" name="submit" class="submit" />
</form>

{include file="CoreAdminHome/templates/footer.tpl"}
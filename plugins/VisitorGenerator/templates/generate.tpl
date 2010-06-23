{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'VisitorGenerator_VisitorGenerator'|translate}</h2>

<table class="adminTable adminTableNoBorder" style="width: 600px;">
<thead>
    <tr>
        <th>{'VisitorGenerator_Visitors'|translate}</th>
        <th>{'VisitorGenerator_ActionsPerVisit'|translate}</th>
        <th>{'VisitorGenerator_Date'|translate}</th>
    </tr>
</thead>
<tbody>
{foreach from=$dates item=date}
    <tr>
        <td>{$date.visitors}</td>
        <td>{$date.actionsPerVisit}</td>
        <td>{$date.startTime|date_format:"%Y-%m-%d"}</td>
    </tr>
{/foreach}
</tbody>
</table>

<p>{'VisitorGenerator_NbActions'|translate}: {$nbActionsTotal}<br />
{'VisitorGenerator_NbRequestsPerSec'|translate}: {$nbRequestsPerSec}<br />
{$timer}</p>

{include file="CoreAdminHome/templates/footer.tpl"}
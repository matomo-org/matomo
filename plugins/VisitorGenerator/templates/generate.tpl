{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'VisitorGenerator_VisitorGenerator'|translate}</h2>

<table class="entityTable dataTable" style="width: 600px;">
<thead>
    <tr>
        <th>{'General_Visitors'|translate}</th>
        <th>{'General_ColumnActionsPerVisit'|translate}</th>
        <th>{'General_Date'|translate}</th>
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

<p>{'General_NbActions'|translate}: {$nbActionsTotal}<br />
{'VisitorGenerator_NbRequestsPerSec'|translate}: {$nbRequestsPerSec}<br />
{$timer}</p>

{include file="CoreAdminHome/templates/footer.tpl"}
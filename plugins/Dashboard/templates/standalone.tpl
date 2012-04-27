
{include file="Dashboard/templates/header.tpl"}
{include file="CoreHome/templates/period_select.tpl"}
<div id="Dashboard"><ul>
{foreach from=$dashboards item=dashboard}
    <li class="dashboardMenuItem {if $dashboardId == $dashboard.iddashboard}sfHover{/if}"><a href="javascript:$('#dashboardWidgetsArea').dashboard('loadDashboard', {$dashboard.iddashboard});">{$dashboard.name}</a></li>
{/foreach}
</ul></div>
{include file="Dashboard/templates/index.tpl"}
</body>
</html>
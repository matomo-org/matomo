{include file="Dashboard/templates/header.tpl"}
<div id="menuHead">
    {include file="CoreHome/templates/period_select.tpl"}
    <div id="Dashboard"><ul>
    {foreach from=$dashboards item=dashboard}
        <li class="dashboardMenuItem" id="Dashboard_embeddedIndex_{$dashboard.iddashboard}"><a href="javascript:$('#dashboardWidgetsArea').dashboard('loadDashboard', {$dashboard.iddashboard});">{$dashboard.name}</a></li>
    {/foreach}
    </ul></div>
    <div class="clear"></div>
</div>
{ajaxLoadingDiv}
{include file="Dashboard/templates/index.tpl"}
</body>
</html>
<td class="multisites-label label" >
    <a title="View reports" href="index.php?module=CoreHome&action=index&date=%date%&period=%period%&idSite=%idsite%">%name%</a>
    
    <span style="width: 10px; margin-left:3px"> 
	<a target="_blank" title="{'General_GoTo'|translate:"%main_url%"}" href="%main_url%"><img src="plugins/MultiSites/images/link.gif" /></a>
    </span>
</td>
<td class="multisites-column">
    %visits%
</td>
<td class="multisites-column">
    %pageviews%
</td>
{if $displayRevenueColumn}
<td class="multisites-column">
    %revenue%
</td>
{/if}
{if $period!='range'}
	<td style="width:170px">
	    <div class="visits" style="display:none">%visitsSummary%</div>
	    <div class="pageviews"style="display:none">%pageviewsSummary%</div>
		{if $displayRevenueColumn}
	    <div class="revenue"style="display:none">%revenueSummary%</div>
	    {/if}
{/if}
{if $show_sparklines}
<td style="width:180px">
    <div id="sparkline_%idsite%" style="width: 100px; margin: auto">
    	<a target="_blank" href="index.php?module=CoreHome&action=index&date=%date%&period=%period%&idSite=%idsite%" title="{capture assign=dashboardName}{'Dashboard_DashboardOf'|translate:'%name%'}{/capture} {'General_GoTo'|translate:$dashboardName}">%sparkline%</a>
    </div>
</td>
{/if}

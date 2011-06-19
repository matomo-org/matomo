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
    %actions%
</td>
{if $displayRevenueColumn}
<td class="multisites-column">
    %revenue%
</td>
{/if}
{if $period!='range'}
	<td style="width:170px">
	    <div class="visits" style="display:none">%visitsSummary%</div>
	    <div class="actions"style="display:none">%actionsSummary%</div>
		{if $displayRevenueColumn}
	    <div class="revenue"style="display:none">%revenueSummary%</div>
	    {/if}
{/if}
{if $show_sparklines}
<td style="width:180px">
    <div id="sparkline_%idsite%" style="width: 100px; margin: auto">
	%sparkline%
    </div>
</td>
{/if}

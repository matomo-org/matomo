{if $javascriptVariablesToSet.showingAllColumns 
	|| (isset($javascriptVariablesToSet.viewDataTable) && $javascriptVariablesToSet.viewDataTable != 'table')}
{assign var=showSimpleTableIcon value=true}
{/if}		
<div id="dataTableFeatures">
	<span id="dataTableExcludeLowPopulation"></span>
	
	<span id="dataTableSearchPattern">
		<input id="keyword" type="text" length="15" />
		<input type="submit" value="{'General_Search'|translate}" />
	</span>
	
	<span id="dataTablePages"></span>
	<span id="dataTablePrevious">&lsaquo; {'General_Previous'|translate}</span>
	<span id="dataTableNext">{'General_Next'|translate} &rsaquo;</span>
	<div>
		<span id="exportDataTable">
			<span id="exportToFormat" style="display:none;padding-left:4px;">
				<img width="16" height="16" src="{$piwikUrl}themes/default/images/export.png" title="{'General_Export'|translate}" />
				<span id="linksExportToFormat" style="display:none;"> 
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="CSV" filter_limit="100">CSV</a> | 
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="XML" filter_limit="100">XML</a> |
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="JSON" filter_limit="100">Json</a> |
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="PHP" filter_limit="100">Php</a>
				</span>
				<a class="viewDataTable" format="cloud"><img width="16" height="16" src="{$piwikUrl}themes/default/images/tagcloud.png" title="{'General_TagCloud'|translate}" /></a>
				<a class="viewDataTable" format="graphVerticalBar"><img width="16" height="16" src="{$piwikUrl}themes/default/images/chart_bar.png" title="{'General_VBarGraph'|translate}" /></a>
				<a class="viewDataTable" format="graphPie"><img width="16" height="16" src="{$piwikUrl}themes/default/images/chart_pie.png" title="{'General_Piechart'|translate}" /></a>
			</span>
			<span id="exportDataTableShow" style="display:none;padding-left:4px;">
				<img src="{$piwikUrl}plugins/CoreHome/templates/images/more.png" />
			</span>
			{if $javascriptVariablesToSet.show_show_all_columns}
				<span id="showingAllColumns" style="display:none;float:right;padding-right:4px;border-right:1px solid #82A1D2;">
				{if isset($showSimpleTableIcon)}
					<img id="hidingAllColumns" title="Display normal table" src="{$piwikUrl}themes/default/images/table.png" />
				{else}
					<img id="showingAllColumns" title="Display more data" src="{$piwikUrl}themes/default/images/table_more.png" />
				{/if}
				</span>
			{/if}
		</span>

		<span id="loadingDataTable"><img src="{$piwikUrl}themes/default/images/loading-blue.gif" /> {'General_LoadingData'|translate}</span>
	</div>
</div>

<div class="dataTableSpacer" />

<div id="dataTableFeatures">

{if $properties.show_exclude_low_population}
	<span id="dataTableExcludeLowPopulation"></span>
{/if}

{if $properties.show_search}
<span id="dataTableSearchPattern">
	<input id="keyword" type="text" length="15" />
	<input type="submit" value="{'General_Search'|translate}" />
</span>
{/if}

{if $properties.show_offset_information}
	<span id="dataTablePages"></span>
	<span id="dataTablePrevious">&lsaquo; {'General_Previous'|translate}</span>
	<span id="dataTableNext">{'General_Next'|translate} &rsaquo;</span>
{/if}

{if $properties.show_footer_icons}
	<div>
		<span id="dataTableFooterIcons">
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
			<span id="dataTableFooterIconsShow" style="display:none;padding-left:4px;">
				<img src="{$piwikUrl}plugins/CoreHome/templates/images/more.png" />
			</span>
			{if $properties.show_table_all_columns}
				<span id="tableAllColumnsSwitch" style="display:none;float:right;padding-right:4px;border-right:1px solid #82A1D2;">
				{if $javascriptVariablesToSet.viewDataTable != 'table'}
					<img title="{'General_DisplayNormalTable'|translate}" src="{$piwikUrl}themes/default/images/table.png" />
				{else}
					<img title="{'General_DisplayMoreData'|translate}" src="{$piwikUrl}themes/default/images/table_more.png" />
				{/if}
				</span>
			{/if}
		</span>
	</div>
{/if}

<span id="loadingDataTable"><img src="{$piwikUrl}themes/default/images/loading-blue.gif" /> {'General_LoadingData'|translate}</span>
</div>

<div class="dataTableSpacer" />

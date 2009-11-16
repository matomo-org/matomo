<div id="dataTableFeatures">
{if $properties.show_exclude_low_population}
	<span id="dataTableExcludeLowPopulation"></span>
{/if}

{if $properties.show_offset_information}
<div>
	<span id="dataTablePages"></span>
	<span id="dataTablePrevious">&lsaquo; {'General_Previous'|translate}</span>
	<span id="dataTableNext">{'General_Next'|translate} &rsaquo;</span>
</div>
{/if}

{if $properties.show_search}
<span id="dataTableSearchPattern">
	<input id="keyword" type="text" length="15" />
	<input type="submit" value="{'General_Search'|translate}" />
</span>
{/if}

{if $properties.show_footer_icons}
	<div>
		<span id="dataTableFooterIcons">
			<span id="exportToFormat" style="display:none;padding-left:4px;">
				{if $properties.show_export_as_image_icon}
					<span id="dataTableFooterExportAsImageIcon">
						<a href="javascript:piwikHelper.OFC.jquery.popup('{$chartDivId}');"><img title="{'General_ExportAsImage_js'|translate}" src="themes/default/images/image.png" /></a>
					</span>
				{/if}
				<img width="16" height="16" src="themes/default/images/export.png" title="{'General_Export'|translate}" />
				<span id="linksExportToFormat" style="display:none"> 
					<a target="_blank" class="exportToFormat" methodToCall="{$properties.apiMethodToRequestDataTable}" format="CSV" filter_limit="100">CSV</a> | 
					<a target="_blank" class="exportToFormat" methodToCall="{$properties.apiMethodToRequestDataTable}" format="XML" filter_limit="100">XML</a> |
					<a target="_blank" class="exportToFormat" methodToCall="{$properties.apiMethodToRequestDataTable}" format="JSON" filter_limit="100">Json</a> |
					<a target="_blank" class="exportToFormat" methodToCall="{$properties.apiMethodToRequestDataTable}" format="PHP" filter_limit="100">Php</a> | 
					<a target="_blank" class="exportToFormat" methodToCall="{$properties.apiMethodToRequestDataTable}" format="RSS" filter_limit="100" date="last10"><img border="0" src="themes/default/images/feed.png"></a>
				</span>
			{if $properties.show_all_views_icons}
				<a class="viewDataTable" format="cloud"><img width="16" height="16" src="themes/default/images/tagcloud.png" title="{'General_TagCloud'|translate}" /></a>
				<a class="viewDataTable" format="graphVerticalBar"><img width="16" height="16" src="themes/default/images/chart_bar.png" title="{'General_VBarGraph'|translate}" /></a>
				<a class="viewDataTable" format="graphPie"><img width="16" height="16" src="themes/default/images/chart_pie.png" title="{'General_Piechart'|translate}" /></a>
			{/if}
			</span>
			<span id="dataTableFooterIconsShow" style="display:none;padding-left:4px;">
				<img src="plugins/CoreHome/templates/images/more.png" />
			</span>
			
			{if $properties.show_table}
				<span id="tableAllColumnsSwitch" style="display:none;float:right;padding-right:4px;border-right:1px solid #82A1D2;">
				{if $javascriptVariablesToSet.viewDataTable != 'table'}
					<img title="{'General_DisplayNormalTable'|translate}" src="themes/default/images/table.png" />
				{elseif $properties.show_table_all_columns}
					<img title="{'General_DisplayMoreData'|translate}" src="themes/default/images/table_more.png" />
				{/if}
				</span>
			{/if}
			
			{if $properties.show_goals}
			<span id="tableGoals" style="display:none;float:right;padding-right:4px;">
				{if $javascriptVariablesToSet.viewDataTable != 'tableGoals'}
					<img title="View Goals" src="themes/default/images/goal.png" />
				{/if}
			</span>
			{/if}
		</span>
	</div>
{/if}

<span id="loadingDataTable"><img src="themes/default/images/loading-blue.gif" /> {'General_LoadingData'|translate}</span>
</div>

<div class="dataTableSpacer" />

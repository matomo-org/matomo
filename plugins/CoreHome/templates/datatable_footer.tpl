<div class="dataTableFeatures">

{if $properties.show_offset_information}
<span>
	<span class="dataTablePages"></span>
</span>
{/if}

{if $properties.show_pagination_control}
<span>
	<span class="dataTablePrevious">&lsaquo; {if isset($javascriptVariablesToSet.dataTablePreviousIsFirst)}{'General_First'|translate}{else}{'General_Previous'|translate}{/if} </span> 
	<span class="dataTableNext">{'General_Next'|translate} &rsaquo;</span>
</span>
{/if}

{if $properties.show_search}
<span class="dataTableSearchPattern">
	<input id="keyword" type="text" length="15" />
	<input type="submit" value="{'General_Search'|translate}" />
</span>
{/if}

<span class="loadingPiwik" style='display:none'><img src="themes/default/images/loading-blue.gif" /> {'General_LoadingData'|translate}</span>
{if $properties.show_footer_icons}
	<div class="dataTableFooterIcons">
		<div class="dataTableFooterWrap" var="{$javascriptVariablesToSet.viewDataTable}">
			{if !$properties.hide_all_views_icons}
			<img src="themes/default/images/data_table_footer_active_item.png" class="dataTableFooterActiveItem" />
			{/if}
			<div class="tableIconsGroup">
            	<span class="tableAllColumnsSwitch">
                    {if $properties.show_table}
                    <a class="tableIcon" format="table" var="table"><img title="{'General_DisplaySimpleTable'|translate}" src="themes/default/images/table.png" /></a>
                    {/if}
                    {if $properties.show_table_all_columns}
                    <a class="tableIcon" format="tableAllColumns" var="tableAllColumns"><img title="{'General_DisplayTableWithMoreMetrics'|translate}" src="themes/default/images/table_more.png" /></a>
                    {/if}
                    {if $properties.show_goals}
					<a class="tableIcon" format="tableGoals" var="tableGoals"><img title="{'General_DisplayTableWithGoalMetrics'|translate}" src="themes/default/images/{if $javascriptVariablesToSet.idGoal=='ecommerceOrder'}ecommerceOrder.gif{else}goal.png{/if}" /></a>
                    {/if}
                    {if $properties.show_ecommerce}
                    <a class="tableIcon" format="ecommerceOrder" var="ecommerceOrder"><img title="{'General_EcommerceOrders'|translate}" src="themes/default/images/ecommerceOrder.gif" /> <span>{'General_EcommerceOrders'|translate}</span></a>
                    <a class="tableIcon" format="ecommerceAbandonedCart" var="ecommerceAbandonedCart"><img title="{'General_AbandonedCarts'|translate}" src="themes/default/images/ecommerceAbandonedCart.gif" /> <span>{'General_AbandonedCarts'|translate}</span></a>
                    {/if}
                </span>
           </div>
            {if $properties.show_all_views_icons}
			<div class="tableIconsGroup">
            	<span class="tableGraphViews tableGraphCollapsed">
                    {if $properties.show_bar_chart}<a class="tableIcon" format="graphVerticalBar" var="graphVerticalBar"><img width="16" height="16" src="themes/default/images/chart_bar.png" title="{'General_VBarGraph'|translate}" /></a>{/if}
                    {if $properties.show_pie_chart}<a class="tableIcon" format="graphPie" var="graphPie"><img width="16" height="16" src="themes/default/images/chart_pie.png" title="{'General_Piechart'|translate}" /></a>{/if}
                    {if $properties.show_tag_cloud}<a class="tableIcon" format="cloud" var="cloud"><img width="16" height="16" src="themes/default/images/tagcloud.png" title="{'General_TagCloud'|translate}" /></a>{/if}
				</span>
           </div>
           {elseif !$properties.hide_all_views_icons && $javascriptVariablesToSet.viewDataTable == "generateDataChartEvolution"}
			<div class="tableIconsGroup">
            	<span class="tableGraphViews">
                    <a class="tableIcon" format="graphEvolution" var="graphEvolution"><img width="16" height="16" src="themes/default/images/chart_bar.png" title="{'General_VBarGraph'|translate}" /></a>
				</span>
           </div>
           
           {/if}			
           
			<div class="tableIconsGroup">
				<span class="exportToFormatIcons"><a class="tableIcon" var="export"><img width="16" height="16" src="themes/default/images/export.png" title="{'General_ExportThisReport'|translate}" /></a></span>
				<span class="exportToFormatItems" style="display:none"> 
					{'General_Export'|translate}: 
					<a target="_blank" methodToCall="{$properties.apiMethodToRequestDataTable}" format="CSV" filter_limit="{$properties.exportLimit}">CSV</a> | 
					<a target="_blank" methodToCall="{$properties.apiMethodToRequestDataTable}" format="TSV" filter_limit="{$properties.exportLimit}">TSV (Excel)</a> | 
					<a target="_blank" methodToCall="{$properties.apiMethodToRequestDataTable}" format="XML" filter_limit="{$properties.exportLimit}">XML</a> |
					<a target="_blank" methodToCall="{$properties.apiMethodToRequestDataTable}" format="JSON" filter_limit="{$properties.exportLimit}">Json</a> |
					<a target="_blank" methodToCall="{$properties.apiMethodToRequestDataTable}" format="PHP" filter_limit="{$properties.exportLimit}">Php</a>
					{if $properties.show_export_as_rss_feed}
						| <a target="_blank" methodToCall="{$properties.apiMethodToRequestDataTable}" format="RSS" filter_limit="{$properties.exportLimit}" date="last10"><img border="0" src="themes/default/images/feed.png" /></a>
					{/if}
				</span>
				{if $properties.show_export_as_image_icon}
					<span id="dataTableFooterExportAsImageIcon">
						<a class="tableIcon" href="#" onclick="$('#{$chartDivId}').trigger('piwikExportAsImage'); return false;"><img title="{'General_ExportAsImage_js'|translate}" src="themes/default/images/image.png" /></a>
					</span>
				{/if}
			</div>
			
		</div>
        <div class="limitSelection {if !$properties.show_pagination_control} hidden{/if}" title="{'General_RowsToDisplay'|translate:escape:'html'}"></div>
		<div class="tableConfiguration">
			<a class="tableConfigurationIcon" href="#"></a>
			<ul>
				{if isset($javascriptVariablesToSet.flat) && $javascriptVariablesToSet.flat == 1}
					<li><div class="configItem dataTableIncludeAggregateRows"></div></li>
				{/if}
				<li><div class="configItem dataTableFlatten"></div></li>
				{if $properties.show_exclude_low_population}
					<li><div class="configItem dataTableExcludeLowPopulation"></div></li>
				{/if}
			</ul>
		</div>
	</div>
{/if}

<div class="datatableRelatedReports">
	{if !empty($properties.relatedReports) && (!empty($arrayDataTable) || !empty($cloudValues) || (isset($isDataAvailable) && $isDataAvailable))}
		{if count($properties.relatedReports) == 1}{'General_RelatedReport'|translate}{else}{'General_RelatedReports'|translate}{/if}:
		<ul style="list-style:none;{if count($properties.relatedReports) == 1}display:inline-block;{/if}">
			<li><span href="{$properties.self_url}" style="display:none;">{$properties.title}</span></li>
			{foreach from=$properties.relatedReports key=reportUrl item=reportTitle}
				<li><span href="{$reportUrl}">{$reportTitle}</span></li>
			{/foreach}
		</ul>
	{/if}
</div>

{if !empty($properties.show_footer_message)}
	<div class='datatableFooterMessage'>{$properties.show_footer_message}</div>
{/if}

</div>

<div class="dataTableSpacer"></div>

<div id="dataTableFeatures">
	<span id="dataTableExcludeLowPopulation"></span>
	
	<span id="dataTableSearchPattern">
		<input id="keyword" type="text" length="15" />
		<input type="submit" value="Search" />
	</span>
	
	<span id="dataTablePages"></span>
	<span id="dataTablePrevious">&lt; Previous</span>
	<span id="dataTableNext">Next &gt;</span>
	<div>
		<span id="exportDataTable">
			<span id="exportToFormat" style="display:none">
				<img width="16" height="16" src="themes/default/images/export.png" />
				<span id="linksExportToFormat" style="display:none"> 
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="CSV" filter_limit="100">CSV</a> | 
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="XML" filter_limit="100">XML</a> |
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="JSON" filter_limit="100">Json</a> |
					<a target="_blank" class="exportToFormat" methodToCall="{$method}" format="PHP" filter_limit="100">Php</a>
				</span>
				<a class="viewDataTable" format="table"><img width="16" height="16" src="themes/default/images/table.png" title="Table" /></a>
				<a class="viewDataTable" format="cloud"><img width="16" height="16" src="themes/default/images/tagcloud.png" title="Tag Cloud" /></a>
				<a class="viewDataTable" format="graphVerticalBar"><img width="16" height="16" src="themes/default/images/chart_bar.png" title="Vertical bar graph" /></a>
				<a class="viewDataTable" format="graphPie"><img width="16" height="16" src="themes/default/images/chart_pie.png" title="Pie chart" /></a>
			</span>
			<span id="exportDataTableShow" style="display:none">
				<img src="plugins/Home/templates/images/more.png" />
			</span>
		</span>

		<span id="loadingDataTable"><img src="themes/default/images/loading-blue.gif" /> Loading...</span>
	</div>
</div>

<div class="dataTableSpacer" />

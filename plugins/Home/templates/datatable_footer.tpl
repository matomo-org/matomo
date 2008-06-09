<div id="dataTableFeatures">
	<span id="dataTableExcludeLowPopulation"></span>
	
	<span id="dataTableSearchPattern">
		<input id="keyword" type="text" length="15" />
		<input type="submit" value="{'General_Search'|translate}" />
	</span>
	
	<span id="dataTablePages"></span>
	<span id="dataTablePrevious">&lt; {'General_Previous'|translate}</span>
	<span id="dataTableNext">{'General_Next'|translate} &gt;</span>
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
				<a class="viewDataTable" format="table"><img width="16" height="16" src="themes/default/images/table.png" title="{'General_Table'|translate}" /></a>
				<a class="viewDataTable" format="cloud"><img width="16" height="16" src="themes/default/images/tagcloud.png" title="{'General_TagCloud'|translate}" /></a>
				<a class="viewDataTable" format="graphVerticalBar"><img width="16" height="16" src="themes/default/images/chart_bar.png" title="{'General_VBarGraph'|translate}" /></a>
				<a class="viewDataTable" format="graphPie"><img width="16" height="16" src="themes/default/images/chart_pie.png" title="{'General_Piechart'|translate}" /></a>
			</span>
			<span id="exportDataTableShow" style="display:none">
				<img src="plugins/Home/templates/images/more.png" />
			</span>
		</span>

		<span id="loadingDataTable"><img src="themes/default/images/loading-blue.gif" /> {'General_LoadingData'|translate}</span>
	</div>
</div>

<div class="dataTableSpacer" />

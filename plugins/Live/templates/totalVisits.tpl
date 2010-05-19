<div id="visitsTotal">
	<table class="dataTable" cellspacing="0">
	<thead>
	<tr>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">{'Live_Date'|translate}</div></th>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">{'General_ColumnNbVisits'|translate}</div></th>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">{'General_ColumnPageviews'|translate}</div></th>
	</tr>
	</thead>
	<tbody>
	<tr class="">
	<td class="columnodd">{'General_Today'|translate}</td>
	<td class="columnodd">{$visitorsCountToday}</td>
	<td class="columnodd">{$pisToday}</td>
	</tr>
	<tr class="">
	<td class="columnodd">{'Live_Last30Minutes'|translate}</td>
	<td class="columnodd">{$visitorsCountHalfHour}</td>
	<td class="columnodd">{$pisHalfhour}</td>
	</tr>
	</tbody>	
	</table>
</div>

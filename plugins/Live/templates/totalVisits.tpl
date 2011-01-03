<div id="visitsTotal">
	<table class="dataTable" cellspacing="0">
	<thead>
	<tr>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">{'General_Date'|translate}</div></th>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">{'General_ColumnNbVisits'|translate}</div></th>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">{'General_ColumnPageviews'|translate}</div></th>
	</tr>
	</thead>
	<tbody>
	<tr class="">
	<td class="columnodd">{'Live_LastHours'|translate:24}</td>
	<td class="columnodd">{$visitorsCountToday}</td>
	<td class="columnodd">{$pisToday}</td>
	</tr>
	<tr class="">
	<td class="columnodd">{'Live_LastMinutes'|translate:30}</td>
	<td class="columnodd">{$visitorsCountHalfHour}</td>
	<td class="columnodd">{$pisHalfhour}</td>
	</tr>
	</tbody>	
	</table>
</div>

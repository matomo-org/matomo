{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{include file="CoreAdminHome/templates/menu.tpl"}
{literal}
<style>
.widefat {
	border-width: 1px;
	border-style: solid;
	border-collapse: collapse;
	width: 100%;
	clear: both;
	margin: 0;
}

.widefat a {
	text-decoration: none;
}

.widefat abbr {
	white-space: nowrap;
}

.widefat td, .widefat th {
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #ccc;
	font-size: 11px;
	vertical-align: text-top;
}

.widefat td {
	padding: 7px 15px 9px 10px;
	vertical-align: top;
}

.widefat th {
	padding: 9px 15px 6px 10px;
	text-align: left;
	line-height: 1.3em;
}

.widefat th input {
	margin: 0 0 0 8px;
	padding: 0;
}

.widefat .check-column {
	text-align: right;
	width: 1.5em;
	padding: 0;

}
.widefat {
	border-color: #ccc;
}

.widefat tbody th.check-column {
	padding: 8px 0 22px;
}
.widefat .num {
	text-align: center;
}
.widefat td, .widefat th, div#available-widgets-filter, ul#widget-list li.widget-list-item, .commentlist li {
	border-bottom-color: #ccc;
}

.widefat thead, .thead {
	background-color: #464646;
	color: #d7d7d7;
}

.widefat td.action-links, .widefat th.action-links {
	text-align: right;
}

.widefat .name {
	font-weight: bold;
}

.widefat a {
	color:#2583AD;
}

.widefat  .green {
	background-color: #ECF9DD;
}


</style>
{/literal}

<div style="max-width:980px;">
 
<h2>Databases management</h2>
<p>Statistics about the primary database usage.</p>
<table class="widefat">
	<thead>
	<tr>
		<th>Table</th>
		<th>Row number</th>
		<th>Size</th>
		<th>Index Size</th> 
		<tbody id="tables">
		{foreach from=$tablesStatus key=index item=table}
		<tr class="active">
			<td class="green">
				{$table.Name}
			</td> 
			<td class="green">
				{$table.Rows}
			</td> 
			<td class="green">
				{$table.Data_length}
			</td> 
			<td class="green">
				{$table.Index_length}
			</td> 
		</tr>
		{/foreach}
	</tr>
	</thead>

</table>

</div>
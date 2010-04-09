{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
<div style="max-width:980px;">

<h2>{'DBStats_DatabaseUsage'|translate}</h2>
{assign var=totalSize value=$tablesStatus.Total.Total_length}
<p>{'DBStats_MainDescription'|translate:$totalSize}
<br />
{'DBStats_LearnMore'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>Piwik Auto Archiving</a>"}</p>
<table class="adminTable">
	<thead>
		<th>{'DBStats_Table'|translate}</th>
		<th>{'DBStats_RowCount'|translate}</th>
		<th>{'DBStats_DataSize'|translate}</th>
		<th>{'DBStats_IndexSize'|translate}</th>
		<th>{'DBStats_TotalSize'|translate}</th>
	</thead>
	<tbody id="tables">
		{foreach from=$tablesStatus key=index item=table}
		<tr {if $table.Name == 'Total'}class="active" style="font-weight:bold;"{/if}>
			<td>
				{$table.Name}
			</td> 
			<td>
				{$table.Rows}
			</td> 
			<td>
				{$table.Data_length}b
			</td> 
			<td>
				{$table.Index_length}b
			</td> 
			<td>
				{$table.Total_length}b
			</td> 
		</tr>
		{/foreach}
	</tbody>
</table>

</div>

{include file="CoreAdminHome/templates/footer.tpl"}

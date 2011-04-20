<a name ="{$reportId}"/>
<h2 style="color: rgb({$reportTitleTextColor}); font-size: {$reportTitleTextSize}px;">
	{$reportName|escape:"html"}
</h2>

{if empty($reportRows)}
	{'CoreHome_ThereIsNoDataForThisReport'|translate}
{else}
	<table style="border-collapse:collapse; margin-left: 5px">
		<thead style="background-color: rgb({$tableHeaderBgColor}); color: rgb({$tableHeaderTextColor}); font-size: {$reportTableHeaderTextSize}px;">
			{foreach from=$reportColumns item=columnName}
			<th style="font-weight: 400; padding: 6px 0px;">
				&nbsp;{$columnName}&nbsp;&nbsp;
			</th>
			{/foreach}
		</thead>
		<tbody>
			{foreach from=$reportRows item=row key=rowId}
			<tr style="font-size: {$reportTableRowTextSize}px; {cycle delimiter=';' values=";background-color: rgb(`$tableBgColor`)" }">
				{foreach from=$reportColumns key=columnId item=columnName}
				<td style="border-bottom: 1px solid rgb({$tableCellBorderColor}); padding: 5px 0px 5px 5px;">
					{if $columnId eq 'label'}
						{if isset($row[$columnId])}
							{if isset($reportRowsMetadata[$rowId].logo)}
							<img src='{$currentPath}{$reportRowsMetadata[$rowId].logo}'>&nbsp;
							{/if}
							{if isset($reportRowsMetadata[$rowId].url)}
								<a style="color: rgb({$reportTextColor});" href='{$reportRowsMetadata[$rowId].url|escape:"url"}'>
							{/if}
									{$row[$columnId]}
							{if isset($reportRowsMetadata[$rowId].url)}
								</a>
							{/if}
						{/if}
					{else}
						{if empty($row[$columnId])}
							0
						{else}
							{$row[$columnId]}
						{/if}
					{/if}
				</td>
				{/foreach}
			</tr>
			{/foreach}
		</tbody>
	</table>
{/if}
<br/>
<a style="text-decoration:none; color: rgb({$reportTitleTextColor}); font-size: {$reportTableHeaderTextSize}" href="#reportTop">
	{'PDFReports_TopOfReport'|translate}
</a>
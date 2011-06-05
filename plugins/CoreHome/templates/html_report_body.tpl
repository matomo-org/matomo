<a name ="{$reportId}"/>
<h2 style="color: rgb({$reportTitleTextColor}); font-size: {$reportTitleTextSize}pt;">
	{$reportName|escape:"html"}
</h2>

{if empty($reportRows)}
	{'CoreHome_ThereIsNoDataForThisReport'|translate}
{else}
	<table style="border-collapse:collapse; margin-left: 5px">
		<thead style="background-color: rgb({$tableHeaderBgColor}); color: rgb({$tableHeaderTextColor}); font-size: {$reportTableHeaderTextSize}pt;">
			{foreach from=$reportColumns item=columnName}
			<th style="padding: 6px 0px;">
				&nbsp;{$columnName}&nbsp;&nbsp;
			</th>
			{/foreach}
		</thead>
		<tbody>
			{foreach from=$reportRows item=row key=rowId}

			{assign var=rowMetrics value=$row->getColumns()}
			{if isset($reportRowsMetadata[$rowId])}
				{assign var=rowMetadata value=$reportRowsMetadata[$rowId]->getColumns()}
			{/if}

			<tr style="{cycle delimiter=';' values=";background-color: rgb(`$tableBgColor`)" }">
				{foreach from=$reportColumns key=columnId item=columnName}
				<td style="font-size: {$reportTableRowTextSize}pt; border-bottom: 1px solid rgb({$tableCellBorderColor}); padding: 5px 0px 5px 5px;">
					{if $columnId eq 'label'}
						{if isset($rowMetrics[$columnId])}
							{if isset($rowMetadata[$rowId].logo)}
							<img src='{$currentPath}{$rowMetadata[$rowId].logo}'>&nbsp;
							{/if}
							{if isset($rowMetadata[$rowId].url)}
								<a style="color: rgb({$reportTextColor});" href='{$rowMetadata[$rowId].url|escape:"url"}'>
							{/if}
									{$rowMetrics[$columnId]}
							{if isset($rowMetadata[$rowId].url)}
								</a>
							{/if}
						{/if}
					{else}
						{if empty($rowMetrics[$columnId])}
							0
						{else}
							{$rowMetrics[$columnId]}
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
<a style="text-decoration:none; color: rgb({$reportTitleTextColor}); font-size: {$reportBackToTopTextSize}pt" href="#reportTop">
	{'PDFReports_TopOfReport'|translate}
</a>
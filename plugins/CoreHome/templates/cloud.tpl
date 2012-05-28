<div id="{$properties.uniqueId}" class="dataTable">
	{if !empty($reportDocumentation) && $javascriptVariablesToSet.viewDataTable != 'tableGoals'}
		<div class="reportDocumentation"><p>{$reportDocumentation}</p></div>
	{/if}
	<div class="tagCloud">
	{if count($cloudValues) == 0}
		{if $showReportDataWasPurgedMessage}
		<div class="pk-emptyDataTable">{'General_DataForThisTagCloudHasBeenPurged'|translate:$deleteReportsOlderThan}</div>
		{else}
		<div class="pk-emptyDataTable">{'General_NoDataForTagCloud'|translate}</div>
		{/if}
	{else}
		{foreach from=$cloudValues key=word item=value}
		<span title="{$value.word} ({$value.value} {$columnTranslation})" class="word size{$value.size} {* we strike tags with 0 hits *} {if $value.value == 0}valueIsZero{/if}">
		{if false !== $labelMetadata[$value.word].url}<a href="{$labelMetadata[$value.word].url}" target="_blank">{/if}
		{if false !== $labelMetadata[$value.word].logo}<img src="{$labelMetadata[$value.word].logo}" width="{$value.logoWidth}" />{else}
		{$value.wordTruncated}{/if}{if false !== $labelMetadata[$value.word].url}</a>{/if}</span>
		{/foreach}
	{/if}
	</div>
	{if $properties.show_footer}
		{include file="CoreHome/templates/datatable_footer.tpl"}
	{/if}
	{include file="CoreHome/templates/datatable_js.tpl"}
</div>

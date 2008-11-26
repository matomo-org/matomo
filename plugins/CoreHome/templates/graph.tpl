<div id="{$id}">
	<div class="{if $graphType=='evolution'}dataTableGraphEvolutionWrapper{else}dataTableGraphWrapper{/if}">
	{$jsInvocationTag}
	
	{if $properties.show_footer}
		{include file="CoreHome/templates/datatable_footer.tpl"}
		{include file="CoreHome/templates/datatable_js.tpl"}
	{/if}
	</div>
</div>

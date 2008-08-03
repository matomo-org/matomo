<div id="{$id}" class="{if $graphType=='evolution'}parentDivGraphEvolution{else}parentDivGraph{/if}">
{$jsInvocationTag}

{if $showFooter}
	{include file="CoreHome/templates/datatable_footer.tpl"}
	{include file="CoreHome/templates/datatable_js.tpl"}
{/if}

</div>

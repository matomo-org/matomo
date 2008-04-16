<div id="{$id}" class="{if $graphType=='evolution'}parentDivGraphEvolution{else}parentDivGraph{/if}">
{$jsInvocationTag}

{if $showFooter}
	<br/><br/>
	<form class="formEmbedCode" id="{$formId}">
	Embed <input name="embed_code" value="{$codeEmbed}" onclick="javascript:document.getElementById('{$formId}').embed_code.focus();document.getElementById('{$formId}').embed_code.select();" readonly="true" type="text">
	
	| <a target="_blank" href="{$urlData}">{'General_GraphData'|translate}</a>
	</form>
	
	{include file="Home/templates/datatable_footer.tpl"}
	{include file="Home/templates/datatable_js.tpl"}
{/if}

</div>

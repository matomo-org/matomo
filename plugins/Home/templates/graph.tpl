<div id="{$id}" class="parentDiv">
{$jsInvocationTag}
<br/><br/>

<form class="formEmbedCode" id="{$formId}">
Embed <input name="embed_code" value="{$codeEmbed}" onclick="javascript:document.getElementById('{$formId}').embed_code.focus();document.getElementById('{$formId}').embed_code.select();" readonly="true" type="text">

| <a target="_blank" href="{$urlData}">Graph data</a>
</form>

{if $showFooter}
	{include file="Home/templates/datatable_footer.tpl"}
{/if}

{include file="Home/templates/datatable_js.tpl"}
</div>

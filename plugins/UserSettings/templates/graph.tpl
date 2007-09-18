<div id="{$id}" class="parentDiv">
{$jsInvocationTag}
<br/><br/>
<small>
<form name="urlForm" id="{$formId}">
Embed <input name="embed_code" value="{$codeEmbed}" onclick="javascript:document.getElementById('{$formId}').embed_code.focus();document.getElementById('{$formId}').embed_code.select();" readonly="true" type="text">

| <a target="_blank" href="{$urlData}">Graph data</a>
</form>

</small>
	
{include file="UserSettings/templates/datatable_footer.tpl"}
</div>
<div id="{$id}" class="parentDiv">
{$jsInvocationTag}
<br/><br/>
<small>
<form name="urlForm" id="urlForm">
Embed <input 
name="embed_code" 
value="{$codeEmbed|escape}" 
onclick="javascript:document.urlForm.embed_code.focus();document.urlForm.embed_code.select();" 
readonly="true" 
type="text">

| <a target="_blank" href="{$urlData}">Graph data</a>
</form>

</small>
	
{include file="UserSettings/templates/datatable_footer.tpl"}
</div>
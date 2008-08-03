<span id="sitesSelection">Site <select name="idSite" onchange='javascript:this.form.submit()'>
	<optgroup label="Sites">
	   {foreach from=$sites item=info}
	   		<option label="{$info.name}" value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
	   {/foreach}
	</optgroup>
</select>
{hiddenurl idSite=null}
</span>
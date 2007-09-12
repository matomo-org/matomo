
<span id="sitesSelection">
<form action="{$url}" method="post">
Sites: <select name="idSite" onchange="javascript:this.form.submit()">
	<optgroup label="Sites">
	   {foreach from=$sites item=info}
	   		<option label="{$info.name}" value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
	   {/foreach}
	</optgroup>
</select>
</form>
</span>
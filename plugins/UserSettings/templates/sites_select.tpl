<!-- sites selection id="sites" -->
<div id="sites">
Sites: 
<form action="{$url}" method="post">
{*<fieldset>
	<legend> &nbsp; Sites &nbsp; </legend>*}
	<p>
	<select name="idSite" onchange="javascript:this.form.submit()">
		<optgroup label="Sites">
		   {foreach from=$sites item=info}
		   		<option label="{$info.name}" value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
		   {/foreach}
		</optgroup>
	</select>
	</p>
{*</fieldset>*}
</form>
</div>
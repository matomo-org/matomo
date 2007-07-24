{section name=consts loop=$consts}
<a name="const{$consts[consts].const_name}" id="{$consts[consts].const_name}"><!-- --></A>
<div class="{cycle values="evenrow,oddrow"}">

	<div class="const-header">
		<img src="{$subdir}media/images/{if $consts[consts].access == 'private'}PrivateVariable{else}Variable{/if}.png" />
		<span class="const-title">
			<span class="const-name">{$consts[consts].const_name}</span>
			 = <span class="const-default">{$consts[consts].const_value|replace:"\n":"<br />"}</span>
			(line <span class="line-number">{if $consts[consts].slink}{$consts[consts].slink}{else}{$consts[consts].line_number}{/if}</span>)
		</span>
	</div>

	{include file="docblock.tpl" sdesc=$consts[consts].sdesc desc=$consts[consts].desc tags=$consts[consts].tags}	
	
</div>
{/section}


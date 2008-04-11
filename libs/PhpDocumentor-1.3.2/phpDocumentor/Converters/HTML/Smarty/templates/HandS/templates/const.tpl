{section name=consts loop=$consts}
<a name="const{$consts[consts].const_name}" id="{$consts[consts].const_name}"><!-- --></A>
<div class="{cycle values="evenrow,oddrow"}">

	<div class="var-header">
		<span class="var-title">
			<span class="var-name">{$consts[consts].const_name}</span>
			 = <span class="var-default">{$consts[consts].const_value|replace:"\n":"<br />"}</span>
			<span class="smalllinenumber">[line {if $consts[consts].slink}{$consts[consts].slink}{else}{$consts[consts].line_number}{/if}]</span>
		</span>
	</div>

	{include file="docblock.tpl" sdesc=$consts[consts].sdesc desc=$consts[consts].desc}
	{include file="tags.tpl" api_tags=$consts[consts].api_tags info_tags=$consts[consts].info_tags}

	<br />
	<div class="top">[ <a href="#top">Top</a> ]</div>
</div>
{/section}

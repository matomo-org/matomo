{section name=glob loop=$globals}
<a name="{$globals[glob].global_link}" id="{$globals[glob].global_link}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">
	
	<div>
		<img src="{$subdir}media/images/Global.png" />
		<span class="var-title">
			<span class="var-type">{$globals[glob].global_type}</span>
			<span class="var-name">{$globals[glob].global_name}</span>
			{if $vars[vars].var_default} = <span class="var-default">{$globals[glob].global_value|replace:"\n":"<br />"}</span>{/if}
			(line <span class="line-number">{if $globals[glob].slink}{$globals[glob].slink}{else}{$globals[glob].line_number}{/if}</span>)
		</span>
	</div>

	{include file="docblock.tpl" sdesc=$globals[glob].sdesc desc=$globals[glob].desc tags=$globals[glob].tags}
	
	{if $globals[glob].global_conflicts.conflict_type}
		<hr class="separator" />
		<div><span class="warning">Conflicts with global variables:</span><br /> 
			{section name=me loop=$globals[glob].global_conflicts.conflicts}
				{$globals[glob].global_conflicts.conflicts[me]}<br />
			{/section}
		</div>
	{/if}
	
</div>
{/section}
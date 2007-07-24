{section name=def loop=$defines}
<a name="{$defines[def].define_link}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">
	
	<div>
		<img src="{$subdir}media/images/Constant.png" />
		<span class="const-title">
			<span class="const-name">{$defines[def].define_name}</span> = {$defines[def].define_value|replace:"\n":"<br />"}
			(line <span class="line-number">{if $defines[def].slink}{$defines[def].slink}{else}{$defines[def].line_number}{/if}</span>)
		</span>
	</div>
	
	{include file="docblock.tpl" sdesc=$defines[def].sdesc desc=$defines[def].desc tags=$defines[def].tags}
	
	{if $globals[glob].global_conflicts.conflict_type}
		<hr class="separator" />
		<div><span class="warning">Conflicts with constants:</span><br /> 
			{section name=me loop=$defines[def].define_conflicts.conflicts}
				{$defines[def].define_conflicts.conflicts[me]}<br />
			{/section}
		</div>
	{/if}
	
</div>
{/section}
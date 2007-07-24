{section name=func loop=$functions}
<a name="{$functions[func].function_dest}" id="{$functions[func].function_dest}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">
	
	<div>
		<span class="method-title">{$functions[func].function_name}</span> (line <span class="line-number">{if $functions[func].slink}{$functions[func].slink}{else}{$functions[func].line_number}{/if}</span>)
	</div> 

	{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc tags=$functions[func].tags params=$functions[func].params function=false}
	
	<div class="method-signature">
		<span class="method-result">{$functions[func].function_return}</span>
		<span class="method-name">
			{if $functions[func].ifunction_call.returnsref}&amp;{/if}{$functions[func].function_name}
		</span>
		{if count($functions[func].ifunction_call.params)}
			({section name=params loop=$functions[func].ifunction_call.params}{if $smarty.section.params.iteration != 1}, {/if}{if $functions[func].ifunction_call.params[params].hasdefault}[{/if}<span class="var-type">{$functions[func].ifunction_call.params[params].type}</span>&nbsp;<span class="var-name">{$functions[func].ifunction_call.params[params].name}</span>{if $functions[func].ifunction_call.params[params].hasdefault} = <span class="var-default">{$functions[func].ifunction_call.params[params].default|escape:"html"}</span>]{/if}{/section})
		{else}
		()
		{/if}
	</div>

	{if $functions[func].params}
		<ul class="parameters">
		{section name=params loop=$functions[func].params}
			<li>
				<span class="var-type">{$functions[func].params[params].datatype}</span>
				<span class="var-name">{$functions[func].params[params].var}</span>{if $functions[func].params[params].data}<span class="var-description">: {$functions[func].params[params].data}</span>{/if}
			</li>
		{/section}
		</ul>
	{/if}
	
	{if $functions[func].function_conflicts.conflict_type}
		<hr class="separator" />
		<div><span class="warning">Conflicts with functions:</span><br /> 
			{section name=me loop=$functions[func].function_conflicts.conflicts}
				{$functions[func].function_conflicts.conflicts[me]}<br />
			{/section}
		</div>
	{/if}

</div>
{/section}

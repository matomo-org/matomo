<div id="function{if $show == 'summary'}_summary{/if}">
{section name=func loop=$functions}
{if $show == 'summary'}
function {$functions[func].id}, {$functions[func].sdesc}<br>
{else}
	<a name="{$functions[func].function_dest}"></a>
	<h3>{$functions[func].function_name}</h3>
	<div class="indent">
		<code>{$functions[func].function_return} {if $functions[func].ifunction_call.returnsref}&amp;{/if}{$functions[func].function_name}(
{if count($functions[func].ifunction_call.params)}
{section name=params loop=$functions[func].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}{if $functions[func].ifunction_call.params[params].hasdefault}[{/if}{$functions[func].ifunction_call.params[params].type} {$functions[func].ifunction_call.params[params].name}{if $functions[func].ifunction_call.params[params].hasdefault} = {$functions[func].ifunction_call.params[params].default|escape:"html"}]{/if}
{/section}
{/if})</code>
		<p class="linenumber">[line {if $functions[func].slink}{$functions[func].slink}{else}{$functions[func].line_number}{/if}]</p>
		{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc tags=$functions[func].tags}
		{if $functions[func].function_conflicts.conflict_type}
		<p><b>Conflicts with functions:</b> 
		{section name=me loop=$functions[func].function_conflicts.conflicts}
		{$functions[func].function_conflicts.conflicts[me]}<br />
		{/section}
		</p>
		{/if}

		<h4>Parameters</h4>
		<ul>
		{section name=params loop=$functions[func].params}
			<li>
			<span class="type">{$functions[func].params[params].datatype}</span>
			<b>{$functions[func].params[params].var}</b> 
			- 
			{$functions[func].params[params].data}</li>
		{/section}
		</ul>
	</div>
	<p class="top">[ <a href="#top">Top</a> ]</p>
{/if}
{/section}
</div>

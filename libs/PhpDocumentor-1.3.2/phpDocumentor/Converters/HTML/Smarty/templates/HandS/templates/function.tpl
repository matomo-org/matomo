{section name=func loop=$functions}
<a name="{$functions[func].function_dest}" id="{$functions[func].function_dest}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">

	<div>
		<span class="method-title">{$functions[func].function_name}</span>&nbsp;&nbsp;<span class="smalllinenumber">[line {if $functions[func].slink}{$functions[func].slink}{else}{$functions[func].line_number}{/if}]</span>
	</div>
<br />
	<div class="function">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code-border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">
		<code>{$functions[func].function_return} {if $functions[func].ifunction_call.returnsref}&amp;{/if}{$functions[func].function_name}(
{if count($functions[func].ifunction_call.params)}
{section name=params loop=$functions[func].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}{if $functions[func].ifunction_call.params[params].hasdefault}[{/if}{$functions[func].ifunction_call.params[params].type} {$functions[func].ifunction_call.params[params].name}{if $functions[func].ifunction_call.params[params].hasdefault} = {$functions[func].ifunction_call.params[params].default|escape:"html"}]{/if}
{/section}
&nbsp;
{/if})</code>
    </td></tr></table>
    </td></tr></table>

		{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc}

    {if count($functions[func].params) > 0}
		<strong>Parameters:</strong><br />
			<table border="0" cellspacing="0" cellpadding="0">
		{section name=params loop=$functions[func].params}
			<tr><td class="indent">
				<span class="var-type">{$functions[func].params[params].datatype}</span>&nbsp;&nbsp;</td>
				<td>
				<span class="var-name">{$functions[func].params[params].var}:&nbsp;</span></td>
				<td>
				{if $functions[func].params[params].data}<span class="var-description"> {$functions[func].params[params].data}</span>{/if}
			</td></tr>
		{/section}
		</table>
	{/if}

<br />
	{include file="tags.tpl" api_tags=$functions[func].api_tags info_tags=$functions[func].info_tags}

	{if $functions[func].function_conflicts.conflict_type}
		<hr class="separator" />
		<div><span class="warning">Conflicts with functions:</span><br />
			{section name=me loop=$functions[func].function_conflicts.conflicts}
				{$functions[func].function_conflicts.conflicts[me]}<br />
			{/section}
		</div>
	{/if}
	<br />
	<div class="top">[ <a href="#top">Top</a> ]</div>
	</div>
	</div>
{/section}

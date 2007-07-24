{section name=func loop=$functions}
{if $show == 'summary'}
function {$functions[func].id}, {$functions[func].sdesc}<br />
{else}
  <hr />
	<a name="{$functions[func].function_dest}"></a>
	<h3>{$functions[func].function_name} <span class="smalllinenumber">[line {if $functions[func].slink}{$functions[func].slink}{else}{$functions[func].line_number}{/if}]</span></h3>
	<div class="function">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code_border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">
		<code>{$functions[func].function_return} {if $functions[func].ifunction_call.returnsref}&amp;{/if}{$functions[func].function_name}(
{if count($functions[func].ifunction_call.params)}
{section name=params loop=$functions[func].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}{if $functions[func].ifunction_call.params[params].hasdefault}[{/if}{$functions[func].ifunction_call.params[params].type} {$functions[func].ifunction_call.params[params].name}{if $functions[func].ifunction_call.params[params].hasdefault} = {$functions[func].ifunction_call.params[params].default|escape:"html"}]{/if}
{/section}
{/if})</code>
    </td></tr></table>
    </td></tr></table><br />

		{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc tags=$functions[func].tags}
    <br /><br />
	{if $functions[func].function_conflicts.conflict_type}
	<p><b>Conflicts with functions:</b> 
	{section name=me loop=$functions[func].function_conflicts.conflicts}
	{$functions[func].function_conflicts.conflicts[me]}<br />
	{/section}
	</p>
	{/if}
{* original    {if $functions[func].function_conflicts != ""
		<b>Conflicts:</b> {$functions[func].function_conflicts<br /><br />
    {/if *}

    {if count($functions[func].params) > 0}
		<h4>Parameters</h4>
    <table border="0" cellspacing="0" cellpadding="0">
		{section name=params loop=$functions[func].params}
      <tr>
        <td class="type">{$functions[func].params[params].datatype}&nbsp;&nbsp;</td>
        <td><b>{$functions[func].params[params].var}</b>&nbsp;&nbsp;</td>
        <td>{$functions[func].params[params].data}</td>
      </tr>
		{/section}
		</table>
    {/if}
	<div class="top">[ <a href="#top">Top</a> ]</div><br /><br />
	</div>
{/if}
{/section}

{if $summary}
<!-- =========== FUNCTION SUMMARY =========== -->
<A NAME='function_summary'><!-- --></A>
<H3>Function Summary</H3> 

<UL>
	{section name=func loop=$functions}
	<!-- =========== Summary =========== -->
		<LI><CODE><A HREF="{$functions[func].id}">{$functions[func].function_return} {$functions[func].function_name}()</A></CODE>
		<BR>{$functions[func].sdesc}
	{/section}
</UL>
{else}
<!-- ============ FUNCTION DETAIL =========== -->

<A NAME='function_detail'></A>
<H3>Function Detail</H3>

<UL>
{section name=func loop=$functions}
<A NAME="{$functions[func].function_dest}"><!-- --></A>

<LI><SPAN class="code">{$functions[func].function_return} {$functions[func].function_name}()</SPAN> [line <span class="linenumber">{if $functions[func].slink}{$functions[func].slink}{else}{$functions[func].line_number}{/if}</span>]<br />
<BR><BR>
<SPAN class="type">Usage:</SPAN> <SPAN class="code">{if $functions[func].ifunction_call.returnsref}&amp;{/if}{$functions[func].function_name}(
{if count($functions[func].ifunction_call.params)}
{section name=params loop=$functions[func].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}{if $functions[func].ifunction_call.params[params].hasdefault}[{/if}{$functions[func].ifunction_call.params[params].type} {$functions[func].ifunction_call.params[params].name}{if $functions[func].ifunction_call.params[params].hasdefault} = {$functions[func].ifunction_call.params[params].default|escape:"html"}]{/if}
{/section}
{/if})</SPAN>
<BR><BR>
{if $functions[func].function_conflicts.conflict_type}
<p><b>Conflicts with functions:</b> 
{section name=me loop=$functions[func].function_conflicts.conflicts}
{$functions[func].function_conflicts.conflicts[me]}<br />
{/section}
</p>
{/if}
{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc tags=$functions[func].tags params=$functions[func].params function=true}
<BR>
<p class="top">[ <a href="#top">Top</a> ]</p>
{/section}
</UL>
{/if}
{if $summary}
<!-- =========== GLOBAL VARIABLE SUMMARY =========== -->
<A NAME='global_summary'><!-- --></A>
<H3>Global Variable Summary</H3>

<UL>
	{section name=glob loop=$globals}
		<LI><CODE><A HREF="{$globals[glob].id}">{$globals[glob].global_name}</A></CODE> = <CODE class="varsummarydefault">{$globals[glob].global_value}</CODE>
		<BR>{$globals[glob].sdesc}
	{/section}
</UL>

{else}
<!-- ============ GLOBAL VARIABLE DETAIL =========== -->

<A NAME='global_detail'></A>
<H3>Global Variable Detail</H3>

<UL>
	{section name=glob loop=$globals}
		<A NAME="{$globals[glob].global_link}"><!-- --></A>
		<LI><i>{$globals[glob].global_type}</i> <SPAN class="code">{$globals[glob].global_name}</SPAN> = <CODE class="varsummarydefault">{$globals[glob].global_value}</CODE> [line <span class="linenumber">{if $globals[glob].slink}{$globals[glob].slink}{else}{$globals[glob].line_number}{/if}</span>]<br />
		{if $globals[glob].global_conflicts.conflict_type}
			<p><b>Conflicts with globals:</b> 
			{section name=me loop=$globals[glob].global_conflicts.conflicts}
				{$globals[glob].global_conflicts.conflicts[me]}<br />
			{/section}
		{/if}<BR><BR>
		{include file="docblock.tpl" sdesc=$globals[glob].sdesc desc=$globals[glob].desc tags=$globals[glob].tags}
	{/section}
</UL>
{/if}
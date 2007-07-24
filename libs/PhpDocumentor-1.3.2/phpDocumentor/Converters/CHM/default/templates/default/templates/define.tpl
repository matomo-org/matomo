{if $summary}
<!-- =========== CONSTANT SUMMARY =========== -->
<A NAME='constant_summary'><!-- --></A>
<H3>Constant Summary</H3>

<UL>
	{section name=def loop=$defines}
		<LI><CODE><A HREF="{$defines[def].id}">{$defines[def].define_name}</A></CODE> = <CODE class="varsummarydefault">{$defines[def].define_value}</CODE>
		<BR>{$defines[def].sdesc}
	{/section}
</UL>
{else}
<!-- ============ CONSTANT DETAIL =========== -->

<A NAME='constant_detail'></A>
<H3>Constant Detail</H3>

<UL>
	{section name=def loop=$defines}
		<A NAME="{$defines[def].define_link}"><!-- --></A>
		<LI><SPAN class="code">{$defines[def].define_name}</SPAN> = <CODE class="varsummarydefault">{$defines[def].define_value}</CODE> [line <span class="linenumber">{if $defines[def].slink}{$defines[def].slink}{else}{$defines[def].line_number}{/if}</span>]<br />
		{if $defines[def].define_conflicts.conflict_type}
			<p><b>Conflicts with defines:</b> 
			{section name=me loop=$defines[def].define_conflicts.conflicts}
				{$defines[def].define_conflicts.conflicts[me]}<br />
			{/section}
			</p>
		{/if}
<BR><BR>
		{include file="docblock.tpl" sdesc=$defines[def].sdesc desc=$defines[def].desc tags=$defines[def].tags}
	{/section}
</UL>
{/if}
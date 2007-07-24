{if $summary}
<!-- =========== INCLUDE SUMMARY =========== -->
<A NAME='include_summary'><!-- --></A>
<H3>Include Statements Summary</H3>

<UL>
	{section name=includes loop=$includes}
		<LI><CODE><A HREF="#{$includes[includes].include_file}">{$includes[includes].include_name}</A></CODE> = <CODE class="varsummarydefault">{$includes[includes].include_value}</CODE>
		<BR>{$includes[includes].sdesc}
	{/section}
</UL>
{else}
<!-- ============ INCLUDE DETAIL =========== -->

<A NAME='include_detail'></A>
<H3>Include Statements Detail</H3>

<UL>
	{section name=includes loop=$includes}
		<A NAME="{$includes[includes].include_file}"><!-- --></A>
		<LI><SPAN class="code">{$includes[includes].include_name} file:</SPAN> = <CODE class="varsummarydefault">{$includes[includes].include_value}</CODE> [line <span class="linenumber">{if $includes[includes].slink}{$includes[includes].slink}{else}{$includes[includes].line_number}{/if}</span>]<br />
		<BR><BR>
		{include file="docblock.tpl" sdesc=$includes[includes].sdesc desc=$includes[includes].desc tags=$includes[includes].tags}
	{/section}
</UL>
{/if}
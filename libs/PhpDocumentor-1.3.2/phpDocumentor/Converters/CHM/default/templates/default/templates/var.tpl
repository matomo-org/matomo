{if $show=="summary"}
<!-- =========== VAR SUMMARY =========== -->
<A NAME='var_summary'><!-- --></A>
<H3>Class Variable Summary</H3>

<UL>
	{section name=vars loop=$vars}
	{if $vars[vars].static}
	<!-- =========== Summary =========== -->
		<LI><CODE>static <a href="{$vars[vars].id}">{$vars[vars].var_name}</a></CODE> = <CODE class="varsummarydefault">{$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</CODE>
		<BR>
		{$vars[vars].sdesc}
	{/if}
	{/section}
	{section name=vars loop=$vars}
	{if !$vars[vars].static}
	<!-- =========== Summary =========== -->
		<LI><CODE><a href="{$vars[vars].id}">{$vars[vars].var_name}</a></CODE> = <CODE class="varsummarydefault">{$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</CODE>
		<BR>
		{$vars[vars].sdesc}
	{/if}
	{/section}
</UL>
{else}
<!-- ============ VARIABLE DETAIL =========== -->

<A NAME='variable_detail'></A>

<H3>Variable Detail</H3>

<UL>
{section name=vars loop=$vars}
{if $vars[vars].static}
<A NAME="{$vars[vars].var_dest}"><!-- --></A>
<LI><SPAN class="code">static {$vars[vars].var_name}</SPAN> = <CODE class="varsummarydefault">{$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</CODE> [line <span class="linenumber">{if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}</span>]</LI>
<LI><b>Data type:</b> <CODE class="varsummarydefault">{$vars[vars].var_type}</CODE>{if $vars[vars].var_overrides}<b>Overrides:</b> {$vars[vars].var_overrides}<br>{/if}</LI>
{include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}
<BR>
{/if}
{/section}
{section name=vars loop=$vars}
{if !$vars[vars].static}
<A NAME="{$vars[vars].var_dest}"><!-- --></A>
<LI><SPAN class="code">{$vars[vars].var_name}</SPAN> = <CODE class="varsummarydefault">{$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</CODE> [line <span class="linenumber">{if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}</span>]</LI>
<LI><b>Data type:</b> <CODE class="varsummarydefault">{$vars[vars].var_type}</CODE>{if $vars[vars].var_overrides}<b>Overrides:</b> {$vars[vars].var_overrides}<br>{/if}</LI>
{include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}
<BR>
{/if}
{/section}
</UL>
{/if}
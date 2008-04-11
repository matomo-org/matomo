{if $show=="summary"}
<!-- =========== CONST SUMMARY =========== -->
<A NAME='const_summary'><!-- --></A>
<H3>Class Constant Summary</H3>

<UL>
	{section name=consts loop=$consts}
	<!-- =========== Summary =========== -->
		<LI><CODE><a href="{$consts[consts].id}">{$consts[consts].const_name}</a></CODE> = <CODE class="varsummarydefault">{$consts[consts].const_value|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</CODE>
		<BR>
		{$consts[consts].sdesc}
	{/section}
</UL>
{else}
<!-- ============ VARIABLE DETAIL =========== -->

<A NAME='variable_detail'></A>

<H3>Class Constant Detail</H3>

<UL>
{section name=consts loop=$consts}
<A NAME="{$consts[consts].const_dest}"><!-- --></A>
<LI><SPAN class="code">{$consts[consts].const_name}</SPAN> = <CODE class="varsummarydefault">{$consts[consts].const_value|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</CODE> [line <span class="linenumber">{if $consts[consts].slink}{$consts[consts].slink}{else}{$consts[consts].line_number}{/if}</span>]</LI>
{include file="docblock.tpl" sdesc=$consts[consts].sdesc desc=$consts[consts].desc tags=$consts[consts].tags}
<BR>
{/section}
</UL>
{/if}
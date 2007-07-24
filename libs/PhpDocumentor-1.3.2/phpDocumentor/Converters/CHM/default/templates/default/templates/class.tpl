{include file="header.tpl" eltype="class" hasel=true contents=$classcontents}
<!-- Start of Class Data -->
<H3>
	<SPAN class="type">{if $is_interface}Interface{else}Class{/if}</SPAN> {$class_name}
	<HR>
</H3>
[line <span class="linenumber">{if $class_slink}{$class_slink}{else}{$line_number}{/if}</span>]<br />
<pre>
{section name=tree loop=$class_tree.classes}{$class_tree.classes[tree]}{$class_tree.distance[tree]}{/section}
</pre>
{if $tutorial}
<div class="maintutorial">Class Tutorial: {$tutorial}</div>
{/if}
{if $children}
<SPAN class="type">Classes extended from {$class_name}:</SPAN>
 	{section name=kids loop=$children}
	<dl>
	<dt>{$children[kids].link}</dt>
		<dd>{$children[kids].sdesc}</dd>
	</dl>
	{/section}</p>
{/if}
{if $conflicts.conflict_type}<p class="warning">Conflicts with classes:<br />
	{section name=me loop=$conflicts.conflicts}
	{$conflicts.conflicts[me]}<br />
	{/section}
<p>
{/if}
<SPAN class="type">Location:</SPAN> {$source_location}
<hr>
{include file="docblock.tpl" type="class" sdesc=$sdesc desc=$desc}
<hr>
{include file="var.tpl" show="summary"}
<hr>
{include file="const.tpl" show="summary"}
<hr>
<!-- =========== INHERITED CONST SUMMARY =========== -->
<A NAME='inheritedconst_summary'><!-- --></A>
<H3>Inherited Class Constant Summary</H3>

{section name=iconsts loop=$iconsts}
<H4>Inherited From Class {$iconsts[iconsts].parent_class}</H4>
<UL>
	{section name=iconsts2 loop=$iconsts[iconsts].iconsts}
	<!-- =========== Summary =========== -->
		<LI><CODE>{$iconsts[iconsts].iconsts[iconsts2].link}</CODE> = <CODE class="varsummarydefault">{$iconsts[iconsts].iconsts[iconsts2].value}</CODE>
		<BR>
		{$iconsts[iconsts].iconsts[iconsts2].sdesc}
	{/section}
	</LI>
</UL>
{/section}
<hr>
<!-- =========== INHERITED VAR SUMMARY =========== -->
<A NAME='inheritedvar_summary'><!-- --></A>
<H3>Inherited Class Variable Summary</H3>

{section name=ivars loop=$ivars}
<H4>Inherited From Class {$ivars[ivars].parent_class}</H4>
<UL>
	{section name=ivars2 loop=$ivars[ivars].ivars}
	<!-- =========== Summary =========== -->
		<LI><CODE>{$ivars[ivars].ivars[ivars2].link}</CODE> = <CODE class="varsummarydefault">{$ivars[ivars].ivars[ivars2].default}</CODE>
		<BR>
		{$ivars[ivars].ivars[ivars2].sdesc}
	{/section}
	</LI>
</UL>
{/section}

<hr>
{include file="method.tpl" show="summary"}
<!-- =========== INHERITED METHOD SUMMARY =========== -->
<A NAME='methods_inherited'><!-- --></A>
<H3>Inherited Method Summary</H3> 

{section name=imethods loop=$imethods}
<H4>Inherited From Class {$imethods[imethods].parent_class}</h4>
<UL>
	{section name=im2 loop=$imethods[imethods].imethods}
	<!-- =========== Summary =========== -->
		<LI><CODE>{$imethods[imethods].imethods[im2].link}</CODE><br>
		{$imethods[imethods].imethods[im2].sdesc}
	{/section}
</UL>
{/section}
<hr>
{include file="method.tpl"}
<hr>
{include file="var.tpl"}
<hr>
{include file="const.tpl"}
<hr>
{include file="footer.tpl"}

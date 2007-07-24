{if $show == 'summary'}
<!-- =========== METHOD SUMMARY =========== -->
<A NAME='method_summary'><!-- --></A>
<H3>Method Summary</H3> 

<UL>
	{section name=methods loop=$methods}
	{if $methods[methods].static}
	<!-- =========== Summary =========== -->
		<LI><CODE>static <A HREF='{$methods[methods].id}'>{$methods[methods].function_return} {$methods[methods].function_name}()</A></CODE>
		<BR>{$methods[methods].sdesc}
	{/if}
	{/section}
	{section name=methods loop=$methods}
	{if $methods[methods].static}
	<!-- =========== Summary =========== -->
		<LI><CODE><A HREF='{$methods[methods].id}'>{$methods[methods].function_return} {$methods[methods].function_name}()</A></CODE>
		<BR>{$methods[methods].sdesc}
	{/if}
	{/section}
</UL>

{else}
<!-- ============ METHOD DETAIL =========== -->

<A NAME='method_detail'></A>
<H3>Method Detail</H3>

<UL>
{section name=methods loop=$methods}
{if $methods[methods].static}
<A NAME='{$methods[methods].method_dest}'><!-- --></A>

<h1><A name="{$methods[methods].function_name}"></A>static {$class_name}::{$methods[methods].function_name}</h1>

<p class=method>
<b>static {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(</b>
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}<b>{$methods[methods].ifunction_call.params[params].type}</b>
<i>{$methods[methods].ifunction_call.params[params].name}</i>{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if}<b> );</b>
</p>

{if $methods[methods].descmethod}
	<p>Overridden in child classes as:<br />
	{section name=dm loop=$methods[methods].descmethod}
	<dl>
	<dt>{$methods[methods].descmethod[dm].link}</dt>
		<dd>{$methods[methods].descmethod[dm].sdesc}</dd>
	</dl>
	{/section}</p>
{/if}
{if $methods[methods].method_overrides}
<p>Overrides {$methods[methods].method_overrides.link} ({$methods[methods].method_overrides.sdesc|default:"parent method not documented"})</p>
{/if}
	{if $methods[methods].method_implements}
		<hr class="separator" />
		<div class="notes">Implementation of:</div>
	{section name=imp loop=$methods[methods].method_implements}
		<dl>
			<dt>{$methods[methods].method_implements[imp].link}</dt>
			{if $methods[methods].method_implements[imp].sdesc}
			<dd>{$methods[methods].method_implements[imp].sdesc}</dd>
			{/if}
		</dl>
	{/section}
	{/if}

{include file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc tags=$methods[methods].tags params=$methods[methods].params function=true}
	<p class="top">[ <a href="#top">Top</a> ]</p>
<BR>
{/if}
{/section}

{section name=methods loop=$methods}
{if !$methods[methods].static}
<A NAME='{$methods[methods].method_dest}'><!-- --></A>

<h1><A name="{$methods[methods].function_name}"></A>{$class_name}::{$methods[methods].function_name}</h1>

<p class=method>
<b>{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(</b>
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}<b>{$methods[methods].ifunction_call.params[params].type}</b>
<i>{$methods[methods].ifunction_call.params[params].name}</i>{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if}<b> );</b>
</p>

{if $methods[methods].descmethod}
	<p>Overridden in child classes as:<br />
	{section name=dm loop=$methods[methods].descmethod}
	<dl>
	<dt>{$methods[methods].descmethod[dm].link}</dt>
		<dd>{$methods[methods].descmethod[dm].sdesc}</dd>
	</dl>
	{/section}</p>
{/if}
{if $methods[methods].method_overrides}
<p>Overrides {$methods[methods].method_overrides.link} ({$methods[methods].method_overrides.sdesc|default:"parent method not documented"})</p>
{/if}
	{if $methods[methods].method_implements}
		<hr class="separator" />
		<div class="notes">Implementation of:</div>
	{section name=imp loop=$methods[methods].method_implements}
		<dl>
			<dt>{$methods[methods].method_implements[imp].link}</dt>
			{if $methods[methods].method_implements[imp].sdesc}
			<dd>{$methods[methods].method_implements[imp].sdesc}</dd>
			{/if}
		</dl>
	{/section}
	{/if}

{include file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc tags=$methods[methods].tags params=$methods[methods].params function=true}
	<p class="top">[ <a href="#top">Top</a> ]</p>
<BR>
{/if}
{/section}
</UL>
{/if}
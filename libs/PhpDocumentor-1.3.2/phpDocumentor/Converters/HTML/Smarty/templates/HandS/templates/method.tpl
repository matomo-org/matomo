<a name='method_detail'></a>
{section name=methods loop=$methods}
{if $methods[methods].static}
<a name="method{$methods[methods].function_name}" id="{$methods[methods].function_name}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">

<div class="method-header">
	<span class="method-title">static method {$methods[methods].function_name}</span>&nbsp;&nbsp;<span class="smalllinenumber">[line {if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}]</span>
</div>
<br />

	<div class="function">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code-border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">&nbsp;
		<code>static {$methods[methods].function_return} {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
&nbsp;
{/if})</code>
    </td></tr></table>
    </td></tr></table><br /></div>

	{include file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc}

	{if $methods[methods].params}
		<strong>Parameters:</strong><br />
			<table border="0" cellspacing="0" cellpadding="0">
		{section name=params loop=$methods[methods].params}
			<tr><td class="indent">
				<span class="var-type">{$methods[methods].params[params].datatype}</span>&nbsp;&nbsp;</td>
				<td>
				<span class="var-name">{$methods[methods].params[params].var}:&nbsp;</span></td>
				<td>
				{if $methods[methods].params[params].data}<span class="var-description"> {$methods[methods].params[params].data}</span>{/if}
			</td></tr>
		{/section}
		</table>

	{/if}
<br />
	{include file="tags.tpl" api_tags=$methods[methods].api_tags info_tags=$methods[methods].info_tags}

	{if $methods[methods].method_overrides}
		<hr class="separator" />
		<div class="notes">Redefinition of:</div>
		<dl>
			<dt>{$methods[methods].method_overrides.link}</dt>
			{if $methods[methods].method_overrides.sdesc}
			<dd>{$methods[methods].method_overrides.sdesc}</dd>
			{/if}
		</dl>
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

	{if $methods[methods].descmethod}
		<hr class="separator" />
		<div class="notes">Redefined in descendants as:</div>
		<ul class="redefinitions">
		{section name=dm loop=$methods[methods].descmethod}
			<li>
				{$methods[methods].descmethod[dm].link}
				{if $methods[methods].descmethod[dm].sdesc}
				: {$methods[methods].descmethod[dm].sdesc}
				{/if}
			</li>
		{/section}
		</ul>
	{/if}
	<br />
	<div class="top">[ <a href="#top">Top</a> ]</div>
</div>
{/if}
{/section}

{section name=methods loop=$methods}
{if !$methods[methods].static}
<a name="method{$methods[methods].function_name}" id="{$methods[methods].function_name}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">

<div class="method-header">
	<span class="method-title">{if $methods[methods].ifunction_call.constructor}Constructor {elseif $methods[methods].ifunction_call.destructor}Destructor {/if}{$methods[methods].function_name}</span>&nbsp;&nbsp;<span class="smalllinenumber">[line {if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}]</span>
</div>
<br />

	<div class="function">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code-border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">&nbsp;
		<code>{$methods[methods].function_return} {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
&nbsp;
{/if})</code>
    </td></tr></table>
    </td></tr></table><br /></div>

	{include file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc}

	{if $methods[methods].params}
		<strong>Parameters:</strong><br />
			<table border="0" cellspacing="0" cellpadding="0">
		{section name=params loop=$methods[methods].params}
			<tr><td class="indent">
				<span class="var-type">{$methods[methods].params[params].datatype}</span>&nbsp;&nbsp;</td>
				<td>
				<span class="var-name">{$methods[methods].params[params].var}:&nbsp;</span></td>
				<td>
				{if $methods[methods].params[params].data}<span class="var-description"> {$methods[methods].params[params].data}</span>{/if}
			</td></tr>
		{/section}
		</table>

	{/if}
<br />
	{include file="tags.tpl" api_tags=$methods[methods].api_tags info_tags=$methods[methods].info_tags}

	{if $methods[methods].method_overrides}
		<hr class="separator" />
		<div class="notes">Redefinition of:</div>
		<dl>
			<dt>{$methods[methods].method_overrides.link}</dt>
			{if $methods[methods].method_overrides.sdesc}
			<dd>{$methods[methods].method_overrides.sdesc}</dd>
			{/if}
		</dl>
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

	{if $methods[methods].descmethod}
		<hr class="separator" />
		<div class="notes">Redefined in descendants as:</div>
		<ul class="redefinitions">
		{section name=dm loop=$methods[methods].descmethod}
			<li>
				{$methods[methods].descmethod[dm].link}
				{if $methods[methods].descmethod[dm].sdesc}
				: {$methods[methods].descmethod[dm].sdesc}
				{/if}
			</li>
		{/section}
		</ul>
	{/if}
	<br />
	<div class="top">[ <a href="#top">Top</a> ]</div>
</div>
{/if}
{/section}

{section name=methods loop=$methods}
{if $methods[methods].static}
{if $show == 'summary'}
static method {$methods[methods].function_call}, {$methods[methods].sdesc}<br />
{else}
  <hr />
	<a name="{$methods[methods].method_dest}"></a>
	<h3>static method {$methods[methods].function_name} <span class="smalllinenumber">[line {if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}]</span></h3>
	<div class="function">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code_border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">
		<code>static {$methods[methods].function_return} {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if})</code>
    </td></tr></table>
    </td></tr></table><br />
	
		{include file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc tags=$methods[methods].tags}<br /><br />

{if $methods[methods].descmethod}
	<p>Overridden in child classes as:<br />
	{section name=dm loop=$methods[methods].descmethod}
	<dl>
	<dt>{$methods[methods].descmethod[dm].link}</dt>
		<dd>{$methods[methods].descmethod[dm].sdesc}</dd>
	</dl>
	{/section}</p>
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
{* original    {if $methods[methods].descmethod != ""
    {$methods[methods].descmethod<br /><br />
    {/if *}
{if $methods[methods].method_overrides}Overrides {$methods[methods].method_overrides.link} ({$methods[methods].method_overrides.sdesc|default:"parent method not documented"})<br /><br />{/if}
{* original    {if $methods[methods].method_overrides != ""
    {$methods[methods].method_overrides<br /><br />
    {/if *}

    {if count($methods[methods].params) > 0}
    <h4>Parameters:</h4>
    <div class="tags">
    <table border="0" cellspacing="0" cellpadding="0">
    {section name=params loop=$methods[methods].params}
      <tr>
        <td class="type">{$methods[methods].params[params].datatype}&nbsp;&nbsp;</td>
        <td><b>{$methods[methods].params[params].var}</b>&nbsp;&nbsp;</td>
        <td>{$methods[methods].params[params].data}</td>
      </tr>
    {/section}
    </table>
    </div><br />
    {/if}
    <div class="top">[ <a href="#top">Top</a> ]</div>
  </div>
{/if}
{/if}
{/section}

{section name=methods loop=$methods}
{if !$methods[methods].static}
{if $show == 'summary'}
method {$methods[methods].function_call}, {$methods[methods].sdesc}<br />
{else}
  <hr />
	<a name="{$methods[methods].method_dest}"></a>
	<h3>{if $methods[methods].ifunction_call.constructor}constructor {elseif $methods[methods].ifunction_call.destructor}destructor {else}method {/if}{$methods[methods].function_name} <span class="smalllinenumber">[line {if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}]</span></h3>
	<div class="function">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code_border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">
		<code>{$methods[methods].function_return} {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if})</code>
    </td></tr></table>
    </td></tr></table><br />
	
		{include file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc tags=$methods[methods].tags}<br /><br />

{if $methods[methods].descmethod}
	<p>Overridden in child classes as:<br />
	{section name=dm loop=$methods[methods].descmethod}
	<dl>
	<dt>{$methods[methods].descmethod[dm].link}</dt>
		<dd>{$methods[methods].descmethod[dm].sdesc}</dd>
	</dl>
	{/section}</p>
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
{* original    {if $methods[methods].descmethod != ""
    {$methods[methods].descmethod<br /><br />
    {/if *}
{if $methods[methods].method_overrides}Overrides {$methods[methods].method_overrides.link} ({$methods[methods].method_overrides.sdesc|default:"parent method not documented"})<br /><br />{/if}
{* original    {if $methods[methods].method_overrides != ""
    {$methods[methods].method_overrides<br /><br />
    {/if *}

    {if count($methods[methods].params) > 0}
    <h4>Parameters:</h4>
    <div class="tags">
    <table border="0" cellspacing="0" cellpadding="0">
    {section name=params loop=$methods[methods].params}
      <tr>
        <td class="type">{$methods[methods].params[params].datatype}&nbsp;&nbsp;</td>
        <td><b>{$methods[methods].params[params].var}</b>&nbsp;&nbsp;</td>
        <td>{$methods[methods].params[params].data}</td>
      </tr>
    {/section}
    </table>
    </div><br />
    {/if}
    <div class="top">[ <a href="#top">Top</a> ]</div>
  </div>
{/if}
{/if}
{/section}

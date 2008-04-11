<h2 class="tab">Method Detail</h2>
<!-- ============ METHOD DETAIL =========== -->
<strong>Summary:</strong><br />
<div class="method-summary">
{section name=methods loop=$methods}
{if $methods[methods].static}
    <div class="method-definition">
    static {if $methods[methods].function_return}
        <span class="method-result">{$methods[methods].function_return}</span>
    {/if}
    <a href="#{$methods[methods].method_dest}" title="details" class="method-name">{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}</a>
    {if count($methods[methods].ifunction_call.params)}
    ({section name=params loop=$methods[methods].ifunction_call.params}{if $smarty.section.params.iteration != 1}, {/if}{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}<span class="var-type">{$methods[methods].ifunction_call.params[params].type}</span>&nbsp;<span class="var-name">{$methods[methods].ifunction_call.params[params].name}</span>{if $methods[methods].ifunction_call.params[params].hasdefault} = <span class="var-default">{$methods[methods].ifunction_call.params[params].default}</span>]{/if}{/section})
    {else}
    ()
    {/if}
    </div>
{/if}
{/section}
{section name=methods loop=$methods}
{if !$methods[methods].static}
    <div class="method-definition">
    {if $methods[methods].function_return}
        <span class="method-result">{$methods[methods].function_return}</span>
    {/if}
    <a href="#{$methods[methods].method_dest}" title="details" class="method-name">{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}</a>
    {if count($methods[methods].ifunction_call.params)}
    ({section name=params loop=$methods[methods].ifunction_call.params}{if $smarty.section.params.iteration != 1}, {/if}{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}<span class="var-type">{$methods[methods].ifunction_call.params[params].type}</span>&nbsp;<span class="var-name">{$methods[methods].ifunction_call.params[params].name}</span>{if $methods[methods].ifunction_call.params[params].hasdefault} = <span class="var-default">{$methods[methods].ifunction_call.params[params].default}</span>]{/if}{/section})
    {else}
    ()
    {/if}
    </div>
{/if}
{/section}
</div>
<hr />
<A NAME='method_detail'></A>


{section name=methods loop=$methods}
{if $methods[methods].static}
<a name="{$methods[methods].method_dest}" id="{$methods[methods].method_dest}"><!-- --></a>
<div style="background='{cycle values="#ffffff,#eeeeee"}'"><h4>
<img src="{$subdir}media/images/PublicMethod.gif" border="0" /> <strong class="method">Static Method {$methods[methods].function_name}</strong> (line <span class="linenumber">{if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}</span>)
 </h4> 
<h4><i>{$methods[methods].function_return}</i> <strong>{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if})</strong></h4>
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
<p><strong>Overrides :</strong> {$methods[methods].method_overrides.link} {$methods[methods].method_overrides.sdesc|default:"parent method not documented"}</p>
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
</div>
{/if}
{/section}
{section name=methods loop=$methods}
{if !$methods[methods].static}
<a name="{$methods[methods].method_dest}" id="{$methods[methods].method_dest}"><!-- --></a>
<div style="background='{cycle values="#ffffff,#eeeeee"}'"><h4>
<img src="{$subdir}media/images/{if $methods[methods].ifunction_call.constructor}Constructor{elseif $methods[methods].ifunction_call.destructor}Destructor{else}PublicMethod{/if}.gif" border="0" /> <strong class="method">{if $methods[methods].ifunction_call.constructor}Constructor {elseif $methods[methods].ifunction_call.destructor}Destructor {else}Method {/if}{$methods[methods].function_name}</strong> (line <span class="linenumber">{if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}</span>)
 </h4> 
<h4><i>{$methods[methods].function_return}</i> <strong>{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if})</strong></h4>
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
<p><strong>Overrides :</strong> {$methods[methods].method_overrides.link} {$methods[methods].method_overrides.sdesc|default:"parent method not documented"}</p>
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
</div>
{/if}
{/section}
<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage2" ) );</script>

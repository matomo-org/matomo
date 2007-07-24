{section name=vars loop=$vars}
{if $vars[vars].static}
<a name="var{$vars[vars].var_name}" id="{$vars[vars].var_name}"><!-- --></A>
<div class="{cycle values="evenrow,oddrow"}">

	<div class="var-header">
		<span class="var-title">
			static <span class="var-type">{$vars[vars].var_type}</span>&nbsp;&nbsp;
			<span class="var-name">{$vars[vars].var_name}</span>
			{if $vars[vars].var_default} = <span class="var-default">{$vars[vars].var_default|replace:"\n":"<br />"}</span>{/if}
			<span class="smalllinenumber">[line {if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}]</span>
		</span>
	</div>

	{include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc}
	{include file="tags.tpl" api_tags=$vars[vars].api_tags info_tags=$vars[vars].info_tags}

	{if $vars[vars].var_overrides}
		<hr class="separator" />
		<div class="notes">Redefinition of:</div>
		<dl>
			<dt>{$vars[vars].var_overrides.link}</dt>
			{if $vars[vars].var_overrides.sdesc}
			<dd>{$vars[vars].var_overrides.sdesc}</dd>
			{/if}
		</dl>
	{/if}

	{if $vars[vars].descvar}
		<hr class="separator" />
		<div class="notes">Redefined in descendants as:</div>
		<ul class="redefinitions">
		{section name=vm loop=$vars[vars].descvar}
			<li>
				{$vars[vars].descvar[vm].link}
				{if $vars[vars].descvar[vm].sdesc}
				: {$vars[vars].descvar[vm].sdesc}
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
{section name=vars loop=$vars}
{if !$vars[vars].static}
<a name="var{$vars[vars].var_name}" id="{$vars[vars].var_name}"><!-- --></A>
<div class="{cycle values="evenrow,oddrow"}">

	<div class="var-header">
		<span class="var-title">
			<span class="var-type">{$vars[vars].var_type}</span>&nbsp;&nbsp;
			<span class="var-name">{$vars[vars].var_name}</span>
			{if $vars[vars].var_default} = <span class="var-default">{$vars[vars].var_default|replace:"\n":"<br />"}</span>{/if}
			<span class="smalllinenumber">[line {if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}]</span>
		</span>
	</div>

	{include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc}
	{include file="tags.tpl" api_tags=$vars[vars].api_tags info_tags=$vars[vars].info_tags}

	{if $vars[vars].var_overrides}
		<hr class="separator" />
		<div class="notes">Redefinition of:</div>
		<dl>
			<dt>{$vars[vars].var_overrides.link}</dt>
			{if $vars[vars].var_overrides.sdesc}
			<dd>{$vars[vars].var_overrides.sdesc}</dd>
			{/if}
		</dl>
	{/if}

	{if $vars[vars].descvar}
		<hr class="separator" />
		<div class="notes">Redefined in descendants as:</div>
		<ul class="redefinitions">
		{section name=vm loop=$vars[vars].descvar}
			<li>
				{$vars[vars].descvar[vm].link}
				{if $vars[vars].descvar[vm].sdesc}
				: {$vars[vars].descvar[vm].sdesc}
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

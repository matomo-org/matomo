{if count($defines) > 0}
{section name=def loop=$defines}
<a name="{$defines[def].define_link}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">

	<div>
		<span class="const-title">
			<span class="const-name">{$defines[def].define_name}</span>&nbsp;&nbsp;<span class="smalllinenumber">[line {if $defines[def].slink}{$defines[def].slink}{else}{$defines[def].line_number}{/if}]</span>
		</span>
	</div>
<br />
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code-border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">
		<code>{$defines[def].define_name} = {$defines[def].define_value}</code>
    </td></tr></table>
    </td></tr></table>

    {include file="docblock.tpl" sdesc=$defines[def].sdesc desc=$defines[def].desc}
    {include file="tags.tpl" api_tags=$defines[def].api_tags info_tags=$defines[def].info_tags}
	<br />

	{if $globals[glob].global_conflicts.conflict_type}
		<hr class="separator" />
		<div><span class="warning">Conflicts with constants:</span><br />
			{section name=me loop=$defines[def].define_conflicts.conflicts}
				{$defines[def].define_conflicts.conflicts[me]}<br />
			{/section}
		</div><br />
	{/if}
	<div class="top">[ <a href="#top">Top</a> ]</div>
	<br />
</div>
{/section}
{/if}
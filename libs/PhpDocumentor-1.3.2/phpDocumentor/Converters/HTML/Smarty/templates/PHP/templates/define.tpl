{if count($defines) > 0}
{section name=def loop=$defines}
{if $show == 'summary'}
define constant <a href="{$defines[def].id}">{$defines[def].define_name}</a> = {$defines[def].define_value}, {$defines[def].sdesc}<br>
{else}
  <hr />
	<a name="{$defines[def].define_link}"></a>
	<h3>{$defines[def].define_name} <span class="smalllinenumber">[line {if $defines[def].slink}{$defines[def].slink}{else}{$defines[def].line_number}{/if}]</span></h3>
	<div class="tags">
    <table width="90%" border="0" cellspacing="0" cellpadding="1"><tr><td class="code_border">
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td class="code">
		<code>{$defines[def].define_name} = {$defines[def].define_value}</code>
    </td></tr></table>
    </td></tr></table>

    {include file="docblock.tpl" sdesc=$defines[def].sdesc desc=$defines[def].desc tags=$defines[def].tags}
    <br />
	{if $defines[def].define_conflicts.conflict_type}
	<p><b>Conflicts with defines:</b> 
	{section name=me loop=$defines[def].define_conflicts.conflicts}
	{$defines[def].define_conflicts.conflicts[me]}<br />
	{/section}
	</p>
	{/if}
{* original    {if $defines[def].define_conflicts != ""
		<b>Conflicts:</b> {$defines[def].define_conflicts<br /><br />
    {/if *}
	</div>
	<div class="top">[ <a href="#top">Top</a> ]</div><br /><br />
{/if}
{/section}
{/if}
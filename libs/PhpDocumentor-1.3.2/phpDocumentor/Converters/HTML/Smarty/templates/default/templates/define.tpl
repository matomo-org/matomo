<div id="define{if $show == 'summary'}_summary{/if}">
{section name=def loop=$defines}
{if $show == 'summary'}
define constant <a href="{$defines[def].id}">{$defines[def].define_name}</a> = {$defines[def].define_value}, {$defines[def].sdesc}<br>
{else}
	<a name="{$defines[def].define_link}"></a>
	<h3>{$defines[def].define_name}</h3>
	<div class="indent">
	<p class="linenumber">[line {if $defines[def].slink}{$defines[def].slink}{else}{$defines[def].line_number}{/if}]</p>
	<p><code>{$defines[def].define_name} = {$defines[def].define_value}</code></p>
	{include file="docblock.tpl" sdesc=$defines[def].sdesc desc=$defines[def].desc tags=$defines[def].tags}
	{if $defines[def].define_conflicts.conflict_type}
	<p><b>Conflicts with defines:</b> 
	{section name=me loop=$defines[def].define_conflicts.conflicts}
	{$defines[def].define_conflicts.conflicts[me]}<br />
	{/section}
	</p>
	{/if}
	</div>
	<p class="top">[ <a href="#top">Top</a> ]</p>
{/if}
{/section}
</div>

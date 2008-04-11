<div id="global{if $show == 'summary'}_summary{/if}">
{section name=glob loop=$globals}
{if $show == 'summary'}
global variable <a href="{$globals[glob].id}">{$globals[glob].global_name}</a> = {$globals[glob].global_value}, {$globals[glob].sdesc}<br>
{else}
	<a name="{$globals[glob].global_link}"></a>
	<h3><i>{$globals[glob].global_type}</i> {$globals[glob].global_name}</h3>
	<div class="indent">
	<p class="linenumber">[line {if $globals[glob].slink}{$globals[glob].slink}{else}{$globals[glob].line_number}{/if}]</p>
	{include file="docblock.tpl" sdesc=$globals[glob].sdesc desc=$globals[glob].desc tags=$globals[glob].tags}

	<p><b>Default Value:</b>{$globals[glob].global_value|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</p>
	{if $globals[glob].global_conflicts.conflict_type}
	<p><b>Conflicts with globals:</b> 
	{section name=me loop=$globals[glob].global_conflicts.conflicts}
	{$globals[glob].global_conflicts.conflicts[me]}<br />
	{/section}
	</p>
	{/if}
	</div>
	<p class="top">[ <a href="#top">Top</a> ]</p>
{/if}
{/section}
</div>

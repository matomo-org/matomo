{if count($globals) > 0}
{section name=glob loop=$globals}
<a name="{$globals[glob].global_link}" id="{$globals[glob].global_link}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">

	<div>
		<span class="var-title">
			<span class="var-type">{$globals[glob].global_type}</span>&nbsp;&nbsp;
			<span class="var-name">{$globals[glob].global_name}</span>
			<span class="smalllinenumber">[line {if $globals[glob].slink}{$globals[glob].slink}{else}{$globals[glob].line_number}{/if}]</span>
		</span>
	</div>

  {if $globals[glob].sdesc != ""}
	{include file="docblock.tpl" sdesc=$globals[glob].sdesc desc=$globals[glob].desc}
  {/if}

  <b>Default value:</b>&nbsp;&nbsp;<span class="var-default">{$globals[glob].global_value|replace:" ":"&nbsp;"|replace:"\n":"<br />\n"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span>
<br />
	{include file="tags.tpl" api_tags=$globals[glob].api_tags info_tags=$globals[glob].info_tags}

	{if $globals[glob].global_conflicts.conflict_type}
		<hr class="separator" />
		<div><span class="warning">Conflicts with global variables:</span><br />
			{section name=me loop=$globals[glob].global_conflicts.conflicts}
				{$globals[glob].global_conflicts.conflicts[me]}<br />
			{/section}
		</div>
	{/if}
	<br />
	<div class="top">[ <a href="#top">Top</a> ]</div>
	<br />
</div>
{/section}
{/if}

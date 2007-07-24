{section name=includes loop=$includes}
<a name="{$includes[includes].include_file}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">

	<div>
		<span class="include-title">
			<span class="include-type">{$includes[includes].include_name}</span>
			(<span class="include-name">{$includes[includes].include_value}</span>)
			<span class="smalllinenumber">[line {if $includes[includes].slink}{$includes[includes].slink}{else}{$includes[includes].line_number}{/if}]</span>
		</span>
	</div>

	{include file="docblock.tpl" sdesc=$includes[includes].sdesc desc=$includes[includes].desc tags=$includes[includes].tags}
	{include file="tags.tpl" api_tags=$includes[includes].api_tags info_tags=$includes[includes].info_tags}
		<div class="top">[ <a href="#top">Top</a> ]</div>
		<br />
</div>
{/section}
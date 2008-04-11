{if count($includes) > 0}
Includes:<br>
{section name=includes loop=$includes}
{$includes[includes].include_name}({$includes[includes].include_value}) <span class="linenumber">[line {if $includes[includes].slink}{$includes[includes].slink}{else}{$includes[includes].line_number}{/if}]</span>
<br />
{include file="docblock.tpl" sdesc=$includes[includes].sdesc desc=$includes[includes].desc tags=$includes[includes].tags}
{/section}
{/if}
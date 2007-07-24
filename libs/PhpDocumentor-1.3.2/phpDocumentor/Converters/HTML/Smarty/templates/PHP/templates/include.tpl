{if count($includes) > 0}
<h4>Includes:</h4>
<div class="tags">
{section name=includes loop=$includes}
{$includes[includes].include_name}({$includes[includes].include_value}) [line {if $includes[includes].slink}{$includes[includes].slink}{else}{$includes[includes].line_number}{/if}]<br />
{include file="docblock.tpl" sdesc=$includes[includes].sdesc desc=$includes[includes].desc tags=$includes[includes].tags}
{/section}
</div>
{/if}
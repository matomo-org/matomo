{include file="header.tpl" noleftindex=true}
<h1>{$title}</h1>
{if $interfaces}
{section name=classtrees loop=$interfaces}
<hr />
<div class="classtree">Root interface {$interfaces[classtrees].class}</div><br />
{$interfaces[classtrees].class_tree}
{/section}
{/if}
{if $classtrees}
{section name=classtrees loop=$classtrees}
<hr />
<div class="classtree">Root class {$classtrees[classtrees].class}</div><br />
{$classtrees[classtrees].class_tree}
{/section}
{/if}
{include file="footer.tpl"}

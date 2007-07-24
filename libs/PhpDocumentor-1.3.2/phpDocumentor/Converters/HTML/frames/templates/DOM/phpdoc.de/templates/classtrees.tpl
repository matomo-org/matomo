{include file="header.tpl" top1=true}

<!-- Start of Class Data -->
<H2>
	{$smarty.capture.title}
</H2>
{if $interfaces}
{section name=classtrees loop=$interfaces}
<h2>Root interface {$interfaces[classtrees].class}</h2>
{$interfaces[classtrees].class_tree}
{/section}
{/if}
{if $classtrees}
{section name=classtrees loop=$classtrees}
<h2>Root class {$classtrees[classtrees].class}</h2>
{$classtrees[classtrees].class_tree}
{/section}
{/if}
{include file="footer.tpl"}
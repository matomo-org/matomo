{capture name="title"}Class Trees for Package {$package}{/capture}
{include file="header.tpl" title=$smarty.capture.title}

<!-- Start of Class Data -->
<H2>
	{$smarty.capture.title}
</H2>
{section name=classtrees loop=$classtrees}
<SPAN class="code">Root class {$classtrees[classtrees].class}</SPAN>
<code class="vardefaultsummary">{$classtrees[classtrees].class_tree}</code>
{/section}
{include file="footer.tpl"}
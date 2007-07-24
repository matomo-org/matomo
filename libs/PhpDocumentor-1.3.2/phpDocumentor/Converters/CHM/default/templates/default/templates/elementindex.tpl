{include file="header.tpl" noleftindex=true}
<a name="top"></a>
<h1>Index of All Elements</h1>
<b>Indexes by package:</b><br>
{section name=p loop=$packageindex}
<a href="elementindex_{$packageindex[p].title}.html">{$packageindex[p].title}</a><br>
{/section}<br>
{include file="basicindex.tpl" indexname="elementindex"}
{include file="footer.tpl"}

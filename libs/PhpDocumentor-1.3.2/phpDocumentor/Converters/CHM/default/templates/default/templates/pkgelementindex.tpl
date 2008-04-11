{include file="header.tpl"}
<a name="top"></a>
<h1>Element index for package {$package}</h1>
{if count($packageindex) > 1}
<b>Indexes by package:</b><br>
{/if}
{section name=p loop=$packageindex}
{if $packageindex[p].title != $package}
<a href="elementindex_{$packageindex[p].title}.html">{$packageindex[p].title}</a><br>
{/if}
{/section}<br>
<a href="elementindex.html"><b>Index of all elements</b></a><br>
{include file="basicindex.tpl" indexname=elementindex_$package}
{include file="footer.tpl"}

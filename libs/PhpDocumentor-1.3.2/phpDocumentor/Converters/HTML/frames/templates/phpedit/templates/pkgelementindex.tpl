{include file="header.tpl"}
<a name="top"></a>
<h2>Element Index, Package  {$package}</h2>
{if count($packageindex) > 1}
<strong>Indexes by package:</strong><br>
{/if}
<ul>
{section name=p loop=$packageindex}
{if $packageindex[p].title != $package}
<li><a href="elementindex_{$packageindex[p].title}.html">{$packageindex[p].title}</a></li>
{/if}
{/section}
</ul>
<a href="elementindex.html"><strong>Index of all elements</strong></a>
<br />
{include file="basicindex.tpl" indexname=elementindex_$package}
{include file="footer.tpl"}

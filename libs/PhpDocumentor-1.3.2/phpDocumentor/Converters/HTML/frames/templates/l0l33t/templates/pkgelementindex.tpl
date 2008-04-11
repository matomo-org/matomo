{include file="header.tpl"}
<a name="top"></a>
<h2>[{$package}] element index</h2>
{if count($packageindex) > 1}
	<h3>Package indexes</h3>
	<ul>
	{section name=p loop=$packageindex}
	{if $packageindex[p].title != $package}
		<li><a href="elementindex_{$packageindex[p].title}.html">{$packageindex[p].title}</a></li>
	{/if}
	{/section}
	</ul>
{/if}
<a href="elementindex.html">All elements</a>
<br />
{include file="basicindex.tpl" indexname=elementindex_$package}
{include file="footer.tpl"}

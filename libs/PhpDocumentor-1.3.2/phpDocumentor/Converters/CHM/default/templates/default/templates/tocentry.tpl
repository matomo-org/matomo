<UL>
{section name=entry loop=$entry}
	<LI> <OBJECT type="text/sitemap">
		<param name="Name" value="{$entry[entry].paramname}">
{if $entry[entry].isclass}		<param name="ImageNumber" value="1">
{/if}{if $entry[entry].outputfile}		<param name="Local" value="{$entry[entry].outputfile}">
{/if}		</OBJECT>
	{if $entry[entry].tocsubentries}{$entry[entry].tocsubentries}{/if}
{/section}
	{$tocsubentries}
</UL>

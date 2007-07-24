{section name=letter loop=$letters}
	<a href="{$indexname}.html#{$letters[letter].letter}">{$letters[letter].letter}</a>
{/section}
<table>
{section name=index loop=$index}
<tr><td colspan = "2"><a name="{$index[index].letter}">&nbsp; </a>
<a href="#top">top</a><br>
<TABLE CELLPADDING='3' CELLSPACING='0' WIDTH='100%' CLASS="border">
	<TR CLASS='TableHeadingColor'>
		<TD>
			<FONT SIZE='+2'><B>{$index[index].letter}</B></FONT>
		</TD>
	</TR>
</TABLE>
</td></tr>
	{section name=contents loop=$index[index].index}
	<tr><td><b>{$index[index].index[contents].name}</b></td><td width="100%" align="left" valign="top">{$index[index].index[contents].listing}</td></tr>
	{/section}
{/section}
</table>


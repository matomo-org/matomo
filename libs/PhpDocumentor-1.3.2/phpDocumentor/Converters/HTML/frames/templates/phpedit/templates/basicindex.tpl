{section name=letter loop=$letters}
	<a href="{$indexname}.html#{$letters[letter].letter}">{$letters[letter].letter}</a>
{/section}

<br /><br />
<table border="0" width="100%">
{section name=index loop=$index}
<thead>
  <tr>
    <td><strong>{$index[index].letter}</strong></td>
    <td align='right'><a name="{$index[index].letter}">&nbsp; </a>
                      <a href="#top">top</a><br /></td>
  </tr>
</thead>
<tbody>
  {section name=contents loop=$index[index].index}
  <tr>
    <td>&nbsp;&nbsp;&nbsp;<strong>{$index[index].index[contents].name}</strong></td>
    <td width="100%" align="left" valign="top">{$index[index].index[contents].listing}</td>
  </tr>
  {/section}
</tbody>
{/section}
</table>

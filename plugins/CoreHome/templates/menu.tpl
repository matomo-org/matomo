<ul class="nav">
{foreach from=$menu key=level1 item=level2 name=menu}
<li>
	<a name='{$level2._url|@urlRewriteWithParameters}' href='#{$level2._url|@urlRewriteWithParameters|substr:1}' onclick='return piwikMenu.onItemClick(this);'>{$level1|translate}</a>
	<ul>
	{foreach from=$level2 key=name item=urlParameters name=level2}
		{if strpos($name, '_') !== 0}
		<li><a name='{$urlParameters._url|@urlRewriteWithParameters}' href='#{$urlParameters._url|@urlRewriteWithParameters|substr:1}' onclick='return piwikMenu.onItemClick(this);'>{$name|translate|escape:'html'}</a></li>
		{/if}
 	{/foreach}
 	</ul>
</li>
{/foreach}
</ul>

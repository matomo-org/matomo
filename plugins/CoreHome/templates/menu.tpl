<ul class="nav">
{foreach from=$menu key=level1 item=level2 name=menu}
<li>
	<a name='{$level2._url|@urlRewriteWithParameters}' href='index.php{$level2._url|@urlRewriteBasicView}'>{$level1|translate}</a>
	<ul>
	{foreach from=$level2 key=name item=urlParameters name=level2}
		{if strpos($name, '_') !== 0}
		<li><a name='{$urlParameters._url|@urlRewriteWithParameters}' href='index.php{$urlParameters._url|@urlRewriteBasicView}'>{$name|translate}</a></li>
		{/if}
 	{/foreach}
 	</ul>
</li>
{/foreach}
</ul>

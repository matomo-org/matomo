
<ul class="nav">
{foreach from=$menu key=level1 item=level2 name=menu}
<li>
	<a name='{$level2._url|@urlRewriteWithParameters}' href='{$level2._url|@urlRewriteBasicView}'>{$level1} &#8595;</a>
	<ul>
	{foreach from=$level2 key=name item=urlParameters name=level2}
		{if $name != '_url'}
			<li><a name='{$urlParameters|@urlRewriteWithParameters}' href='{$urlParameters|@urlRewriteBasicView}'>{$name}</a></li>
		{/if}
 	{/foreach}
 	</ul>
</li>
{/foreach}
</ul>


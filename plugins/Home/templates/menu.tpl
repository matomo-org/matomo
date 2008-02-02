
<ul class="nav">
{foreach from=$menu key=level1 item=level2 name=menu}
<li>
	<a href='{$level2._url|@urlRewriteWithParameters}'>{$level1} &#8595;</a>
	<ul>
	{foreach from=$level2 key=name item=urlParameters name=level2}
		{if $name != '_url'}
			<li><a href='{$urlParameters|@urlRewriteWithParameters}'>{$name}</a></li>
		{/if}
 	{/foreach}
 	</ul>
</li>
{/foreach}
</ul>


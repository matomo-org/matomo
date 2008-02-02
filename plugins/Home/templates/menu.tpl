
<ul class="nav">
{foreach from=$menu key=level1 item=level2 name=menu}
<li {if $smarty.foreach.menu.first}class='sfHover'{/if}>
	<a href='{$level2._url|@urlRewriteWithParameters}'>{$level1} &#8595;</a>
	<ul>
	{foreach from=$level2 key=name item=urlParameters name=level2}
		{if $name != '_url'}
			<li {if $smarty.foreach.menu.first && $smarty.foreach.level2.first}class='sfHover'{/if}><a href='{$urlParameters|@urlRewriteWithParameters}'>{$name}</a></li>
		{/if}
 	{/foreach}
 	</ul>
</li>
{/foreach}
</ul>


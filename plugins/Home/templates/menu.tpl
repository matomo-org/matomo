
<ul class="nav">
{foreach from=$menu key=level1 item=level2}
<li>
	<a href='#'>{$level1} &#8595;</a>
	<ul>
	{foreach from=$level2 key=name item=urlParameters}
		<li><a href='{$urlParameters|@urlRewriteWithParameters}'>{$name}</a></li>
 	{/foreach}
 	</ul>
</li>
{/foreach}
</ul>


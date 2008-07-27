<ul id="tablist">
{foreach from=$menu key=name item=url name=menu}
	<li><a name='{$url|@urlRewriteAdminView}' href='{$url|@urlRewriteAdminView}'>{$name}</a></li>
{/foreach}
</ul>

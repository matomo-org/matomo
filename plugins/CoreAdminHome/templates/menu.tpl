{if count($menu) > 1}
	<div id="menu">
	<ul id="tablist">
	{foreach from=$menu key=name item=url name=menu}
		<li> <a href='index.php{$url._url|@urlRewriteWithParameters}' {if isset($currentAdminMenuName) && $name==$currentAdminMenuName}class='active'{/if}>{$name|translate}</a></li>	{/foreach}
	</ul>
	</div>
{/if}
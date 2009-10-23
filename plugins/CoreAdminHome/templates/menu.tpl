<link rel="stylesheet" type="text/css" href="plugins/CoreAdminHome/templates/menu.css" />

<div id="menu">
<ul id="tablist">
{foreach from=$menu key=name item=url name=menu}
	<li><a href='index.php{$url|@urlRewriteWithParameters}'>{$name|translate}</a></li>
{/foreach}
</ul>
</div>

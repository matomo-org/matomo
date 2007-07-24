{include file="header.tpl" top1=true}
{if count($ric) >= 1}
<ul>
{section name=ric loop=$ric}
	<li><a href="{$ric[ric].file}" target="right">{$ric[ric].name}</a></li>
{/section}
</ul>
{/if}
<h1>Packages</h1>
<ul>
{section name=p loop=$packages}
	<li><a class="package" href='{$packages[p].link}' target='left_bottom'>{$packages[p].title}</a></li>
{/section}
</ul>
</body>
</html>
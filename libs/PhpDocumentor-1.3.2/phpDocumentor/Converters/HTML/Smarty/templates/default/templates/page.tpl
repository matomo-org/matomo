{include file="header.tpl" eltype="Procedural file" class_name=$name hasel=true contents=$pagecontents}

<br>
<br>

<div class="contents">
{if $tutorial}
<span class="maintutorial">Main Tutorial: {$tutorial}</span>
{/if}
<h2>Classes:</h2>
<dl>
{section name=classes loop=$classes}
<dt>{$classes[classes].link}</dt>
	<dd>{$classes[classes].sdesc}</dd>
{/section}
</dl>
</div>

<h2>Page Details:</h2>
{include file="docblock.tpl" type="page"}
<hr>
{include file="include.tpl"}
<hr>
{include file="global.tpl"}
<hr>
{include file="define.tpl"}
<hr>
{include file="function.tpl"}

{include file="footer.tpl"}


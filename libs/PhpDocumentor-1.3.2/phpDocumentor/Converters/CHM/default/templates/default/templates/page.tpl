{include file="header.tpl" eltype="Procedural file"}
<h3><SPAN class="type">File:</SPAN> {$source_location}<HR>
</h3>
{if $tutorial}
<div class="maintutorial">Main Tutorial: {$tutorial}</div>
{/if}
{include file="docblock.tpl" desc=$desc sdesc=$sdesc tags=$tags}
Classes in this file:
<dl>
{section name=classes loop=$classes}
<dt>{$classes[classes].link}</dt>
	<dd>{$classes[classes].sdesc}</dd>
{/section}
</dl>
<hr>
{include file="include.tpl" summary=true}
<hr>
{include file="global.tpl" summary=true}
<hr>
{include file="define.tpl" summary=true}
<hr>
{include file="function.tpl" summary=true}
<hr>
{include file="include.tpl"}
<hr>
{include file="global.tpl"}
<hr>
{include file="define.tpl"}
<hr>
{include file="function.tpl"}
<hr>
{include file="footer.tpl"}

</HTML>
{capture name="tutle"}File Source for {$name}{/capture}
{include file="header.tpl" title=$smarty.capture.tutle}
<h1 align="center">Source for file {$name}</h1>
<p>Documentation is available at {$docs}</p>
<div class="src-code">
{$source}
</div>
{include file="footer.tpl"}
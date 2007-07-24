{capture name="tutle"}File Source for {$name}{/capture}
{include file="header.tpl" title=$smarty.capture.tutle}
<h1 align="center">Source for file {$name}</h1>
<p>Documentation is available at {$docs}</p>
{$source}
{include file="footer.tpl"}
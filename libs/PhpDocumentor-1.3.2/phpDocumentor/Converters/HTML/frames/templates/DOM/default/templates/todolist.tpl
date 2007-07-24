{include file="header.tpl" title="Todo List"}
<div align="center"><h1>Todo List</h1></div>
{foreach from=$todos key=todopackage item=todo}
<h2>{$todopackage}</h2>
{section name=todo loop=$todo}
<h3>{$todo[todo].link}</h3>
<ul>
{section name=t loop=$todo[todo].todos}
    <li>{$todo[todo].todos[t]}</li>
{/section}
</ul>
{/section}
{/foreach}
{include file="footer.tpl"}
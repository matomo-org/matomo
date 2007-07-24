<newpage />
{* Todo List template for the PDF Converter *}
<text size="26" justification="centre"><C:rf:2Appendix D - Todo List>Appendix D - Todo List
</text>
{foreach from=$todos key=todopackage item=todo}
<text size="16" justification="centre">In Package {$todopackage}

</text>
{section name=todo loop=$todo}
<text size="12">In <b>{$todo[todo].link}</b>:
</text>
<text size="11"><ul>{section name=t loop=$todo[todo].todos}
    <li>{$todo[todo].todos[t]}</li>{/section}
</ul>
</text>
{/section}
{/foreach}
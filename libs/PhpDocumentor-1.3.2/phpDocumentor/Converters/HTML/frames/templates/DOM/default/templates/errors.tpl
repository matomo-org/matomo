{include file="header.tpl" noleftindex=true}
{section name=files loop=$files}
<a href="#{$files[files].file}">{$files[files].file}</a><br>
{/section}
{foreach key=file item=issues from=$all}
<a name="{$file}"></a>
<h1>{$file}</h1>
{if count($issues.warnings)}
<h2>Warnings:</h2><br>
{section name=warnings loop=$issues.warnings}
<b>{$issues.warnings[warnings].name}</b> - {$issues.warnings[warnings].listing}<br>
{/section}
{/if}
{if count($issues.errors)}
<h2>Errors:</h2><br>
{section name=errors loop=$issues.errors}
<b>{$issues.errors[errors].name}</b> - {$issues.errors[errors].listing}<br>
{/section}
{/if}
{/foreach}
{include file="footer.tpl"}

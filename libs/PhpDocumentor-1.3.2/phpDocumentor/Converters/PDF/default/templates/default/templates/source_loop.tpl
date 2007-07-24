{* Source Code template for the PDF Converter *}
{foreach from=$source item=code id=$package}
<text size="16" justification="centre"><C:rf:3Package {$package}>Package {$package}
</text>
{section name=code loop=$code}
{$code[code]}
{/section}
{/foreach}
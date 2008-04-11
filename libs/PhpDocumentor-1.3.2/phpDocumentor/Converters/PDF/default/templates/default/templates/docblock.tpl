{if $sdesc}<text size="12" justification="full" left="10"><C:indent:25><b>{$sdesc}</b>
<C:indent:-25>{$desc}
</text>{/if}
{if $tags}
<text size="10" left="15">
<C:indent:40>
<ul>{section name=tags loop=$tags}<li><b>{$tags[tags].keyword}</b> {$tags[tags].data}</li>
{/section}</ul>
<C:indent:-40></text>
{/if}

<pdffunction:ezStopPageNumbers arg="1" arg="1" />
<pdffunction:ezInsertMode arg="1" arg="1" arg="after" />
<newpage />
<text size="26" justification="centre">Contents
</text>
{assign var="xpos" value="520"}
{foreach item=v key=k from=$contents}
{if $v[2] == '1'}
<text size="16" aright="{$xpos}"><c:ilink:toc{$k}>{$v[0]}</c:ilink><C:dots:3{$v[1]}></text>
{elseif $v[2] == '2'}
<text size="12" aright="{$xpos}" left="30"><c:ilink:toc{$k}>{$v[0]}</c:ilink><C:dots:3{$v[1]}></text>
{elseif $v[2] == '3'}
<text size="12" aright="{$xpos}" left="40"><c:ilink:toc{$k}>{$v[0]}</c:ilink><C:dots:3{$v[1]}></text>
{/if}
{/foreach}

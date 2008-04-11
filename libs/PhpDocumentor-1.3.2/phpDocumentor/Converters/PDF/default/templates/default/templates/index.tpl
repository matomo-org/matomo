<pdffunction:ezInsertMode arg="0" />
<newpage />
<text size="26" justification="centre"><C:rf:1Index>Index
</text>
{foreach item="contents" key="letter" from=$indexcontents}
<text size="26"><C:IndexLetter:{$letter}></text>
{foreach item="arr" from=$contents}
<text size="11" aright="520"><c:ilink:toc{$arr[3]}>{$arr[0]}</c:ilink><C:dots:4{$arr[2]}></text>
{if $arr[1]}
<text size="11" left="50"><i>{$arr[1]}</i></text>
{/if}
{/foreach}
{/foreach}


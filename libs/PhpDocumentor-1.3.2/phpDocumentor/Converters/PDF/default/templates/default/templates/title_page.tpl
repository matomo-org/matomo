<pdffunction:ezSetDy arg="-100" />
<text size="30" justification="centre"><b>{$title}</b></text>
<pdffunction:ezSetDy arg="-150" />
{if $logo}
<pdffunction:getYPlusOffset return="newy" offset="0" />
<pdffunction:addJpegFromFile arg="{$logo}" x="250" y=$newy />
{/if}
{capture name="glink"}Global Variable {$name}{/capture}
{capture name="gindex"}global {$name}|||{$sdesc}{/capture}
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="10" justification="left"><b>{$name}</b>
<C:indent:25>
<i>{$type}</i> = {$value} <i>[line {if $slink}{$slink}{else}{$linenumber}{/if}]</i><C:rf:3{$smarty.capture.glink|rawurlencode}><C:index:{$smarty.capture.gindex|rawurlencode}>
<C:indent:-25></text>

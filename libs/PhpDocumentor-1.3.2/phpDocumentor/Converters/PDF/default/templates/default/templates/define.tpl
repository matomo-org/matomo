{capture name="dlink"}Define {$name}{/capture}
{capture name="dindex"}{$name}|||{$sdesc}{/capture}
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="10" justification="left">{$name} = {$value} <i>[line {if $slink}{$slink}{else}{$linenumber}{/if}]</i><C:rf:3{$smarty.capture.dlink|rawurlencode}><C:index:{$smarty.capture.dindex|rawurlencode}></text>

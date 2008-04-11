{capture name="vlink"}Class Constant {$name}{/capture}
{capture name="vindex"}{$class}::{$name}|||{$sdesc}{/capture}
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="10" justification="left"><b>{$class}::{$name}</b>
<C:indent:25>
 = {$value} <i>[line {if $slink}{$slink}{else}{$linenumber}{/if}]</i><C:rf:3{$smarty.capture.vlink|rawurlencode}><C:index:{$smarty.capture.vindex|rawurlencode}>
<C:indent:-25></text>

{capture name="tlink"}{$title|strip_tags}{/capture}
{capture name="tindex"}{$title|strip_tags}|||{/capture}
{capture name="dest"}tutorial{$package}{$subpackage}{$element->name}{/capture}
<newpage />
<pdffunction:addDestination arg="{$smarty.capture.dest|urlencode}" arg="FitH" arg=$this->y />
<text size="26" justification="centre">{$title}<C:rf:{if $hasparent}3{elseif $child}2{else}1{/if}{$smarty.capture.tlink|rawurlencode}><C:index:{$smarty.capture.tindex|rawurlencode}>
</text>{$contents}
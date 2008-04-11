{capture name="clink"}{if $is_interface}Interface{else}Class{/if} {$name}{/capture}
{capture name="cindex"}{$name}|||{$sdesc}{/capture}
{capture name="classeslink"}Package {$package} Classes{/capture}
{if $plink}{capture name="plink"}Package {$package}{/capture}{/if}
{if $includeheader}{include file="newpackage_header.tpl" isclass=true}{/if}
<text size="11">



</text>
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="20" justification="centre">{if $is_interface}Interface{else}Class{/if} {$name} <i></text><text size="11" justification="centre">[line {if $slink}{$slink}{else}{$linenumber}{/if}]</i><C:rf:2{$smarty.capture.clink|rawurlencode}><C:index:{$smarty.capture.cindex|rawurlencode}></text>

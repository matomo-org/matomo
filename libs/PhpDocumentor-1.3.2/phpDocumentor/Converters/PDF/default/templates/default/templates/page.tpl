{capture name="pagelink"}{$name}{/capture}
{capture name="pageindex"}{$name}|||{$sdesc}{/capture}
{capture name="classeslink"}Package {$package} Procedural Elements{/capture}
<newpage />
{if $includeheader}{include file="newpackage_header.tpl" isclass=false}{/if}
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="18" justification="center">{$name}<C:rf:2{$smarty.capture.pagelink|rawurlencode}><C:index:{$smarty.capture.pageindex|rawurlencode}></text>
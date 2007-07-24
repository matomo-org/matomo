<newpage />
{if $ppage}{include file="packagepage.tpl" package=$package plink=$smarty.capture.plink ppage=$ppage}{/if}
<text size="26" justification="centre">Package {$package} {if $isclass}Classes{else}Procedural Elements{/if}<C:rf:1{$smarty.capture.classeslink|rawurlencode}>


</text>

{capture name="tlink"}{$name}{/capture}
{capture name="tindex"}{$name}|||{/capture}
<text size="20" justification="centre"><C:rf:3{$smarty.capture.tlink|rawurlencode}><C:index:{$smarty.capture.tindex|rawurlencode}>{$name}

</text>
<text size="10" justification="left">
{$contents|htmlentities}</text>
{capture name="gindex"}{$name}|||Source code{/capture}
<newpage />
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="26" justification="centre"><C:index:{$smarty.capture.gindex|rawurlencode}><C:rf:3source code: {$name}>File Source for {$name}
</text>
<text size="12"><i>Documentation for this file is available at {$docs}</i>

</text>
<font face="Courier" />
<text size="8">{$source}</text>
<font face="Helvetica" />

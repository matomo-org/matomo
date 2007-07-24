{capture name="flink"}Function {$intricatefunctioncall.name}{/capture}
{capture name="findex"}{$intricatefunctioncall.name}()|||{$sdesc}{/capture}
<pdffunction:addDestination arg="{$dest}" arg="FitH" arg=$this->y />
<text size="10" justification="left"><i>{$return}</i> function {$intricatefunctioncall.name}({section name=params loop=$intricatefunctioncall.params}{if $smarty.section.params.index > 0}, {/if}{if $intricatefunctioncall.params[params].hasdefault}[{/if}{$intricatefunctioncall.params[params].name}{if $intricatefunctioncall.params[params].hasdefault} = {$intricatefunctioncall.params[params].default}]{/if}{/section}) <i>[line {if $slink}{$slink}{else}{$linenumber}{/if}]</i><C:rf:3{$smarty.capture.flink|rawurlencode}><C:index:{$smarty.capture.findex|rawurlencode}></text>

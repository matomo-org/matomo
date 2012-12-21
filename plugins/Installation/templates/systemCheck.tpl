{if !$showNextStep}
{include file="Installation/templates/systemCheck_legend.tpl"}
<br style="clear:both">
{/if}

<h3>{'Installation_SystemCheck'|translate}</h3>
<br/>
{include file="Installation/templates/systemCheckSection.tpl"}

{if !$showNextStep}
<br/><p>
<img src='themes/default/images/link.gif' /> &nbsp;<a href="?module=Proxy&action=redirect&url=http://piwik.org/docs/requirements/" target="_blank">{'Installation_Requirements'|translate}</a> 
</p>
{include file="Installation/templates/systemCheck_legend.tpl"}
{/if}

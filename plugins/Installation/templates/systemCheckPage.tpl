{include file="CoreAdminHome/templates/header.tpl"}

{if $isSuperUser}

<h2>{'Installation_SystemCheck'|translate}</h2>

<p style="margin-left:1em">{if $infos.has_errors}
<img src='themes/default/images/error.png' />  {'Installation_SystemCheckSummaryThereWereErrors'|translate:'<strong>':'</strong>':'<strong><em>':'</em></strong>'} {'Installation_SeeBelowForMoreInfo'|translate}
{elseif $infos.has_warnings}
<img src='themes/default/images/warning.png' />  {'Installation_SystemCheckSummaryThereWereWarnings'|translate} {'Installation_SeeBelowForMoreInfo'|translate}
{else}
<img src='themes/default/images/ok.png' />  {'Installation_SystemCheckSummaryNoProblems'|translate}
{/if}</p>

{include file="Installation/templates/systemCheckSection.tpl"}

{/if}

{include file="CoreAdminHome/templates/footer.tpl"}

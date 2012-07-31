{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'VisitorGenerator_VisitorGenerator'|translate}</h2>

Generated visits for {$siteName} and for {'General_LastDays'|translate:$days}.<br />
Generated {'General_NbActions'|translate}: {$nbActionsTotal}<br />
{'VisitorGenerator_NbRequestsPerSec'|translate}: {$nbRequestsPerSec}<br />
{$timer}</p>
<p><strong> To have Piwik re-process reports for dates that maybe are already processed, you can TRUNCATE the tables piwik_archive_numeric_* for the months you wish to re-generate data. <a href='http://piwik.org/faq/how-to/#faq_59' target="_blank">See FAQ</a></strong>
</p>
{include file="CoreAdminHome/templates/footer.tpl"}

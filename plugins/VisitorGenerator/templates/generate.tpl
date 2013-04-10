{include file="CoreAdminHome/templates/header.tpl"}

<h2>{'VisitorGenerator_VisitorGenerator'|translate}</h2>

Generated visits for {$siteName} and for {'General_LastDays'|translate:$days}.<br/>
Generated {'General_NbActions'|translate}: {$nbActionsTotal}<br/>
{'VisitorGenerator_NbRequestsPerSec'|translate}: {$nbRequestsPerSec}<br/>
{$timer}</p>
<p><strong>
        {if $browserArchivingEnabled}
            The reports will be reprocessed the next time you visit the Piwik reports, it might take a few minutes.
        {else}
            Please re-run the archive.php Piwik script in the crontab to refresh the reports.
            <a href="http://piwik.org/docs/setup-auto-archiving/">See "How to Set up Auto-Archiving of Your Reports"</a>
        {/if}
    </strong>
</p>
{include file="CoreAdminHome/templates/footer.tpl"}

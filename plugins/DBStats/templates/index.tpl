{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='CoreAdminHome CoreHome'}

{literal}
<style>
.dbstatsTable {
	display: inline-block;
}
.dbstatsTable>tbody>tr>td:first-child {
	width: 550px;
}
.dbstatsTable h2 {
	width: 500px;
}
.adminTable.dbstatsTable a {
	color: black;
	text-decoration: underline;
}
</style>
{/literal}

<a name="databaseUsageSummary"></a>
<h2>{'DBStats_DatabaseUsage'|translate}</h2>
<p>
	{'DBStats_MainDescription'|translate:$totalSpaceUsed}<br/>
	{'DBStats_LearnMore'|translate:"<a href='?module=Proxy&action=redirect&url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>Piwik Auto Archiving</a>"}<br/>
	<br/>
</p>
<table class="adminTable dbstatsTable">
	<tbody>
		<tr>
			<td>{$databaseUsageSummary}</td>
			<td>
				<h3 style="margin-top:0">{'General_GeneralInformation'|translate}</h3><br/>
				<p style="font-size:1.4em;padding-left:21px;line-height:1.8em">
					<strong><em>{$userCount}</strong></em>&nbsp;{if $userCount == 1}{'UsersManager_User'|translate}{else}{'UsersManager_MenuUsers'|translate}{/if}<br/>
					<strong><em>{$siteCount}</strong></em>&nbsp;{if $siteCount == 1}{'General_Website'|translate}{else}{'Referers_Websites'|translate}{/if}
				</p><br/>
				{capture assign=clickDeleteLogSettings}{'PrivacyManager_DeleteDataSettings'|translate}{/capture}
				<h3 style="margin-top:0">{'PrivacyManager_DeleteDataSettings'|translate}</h3><br/>
				<p>
					{'PrivacyManager_DeleteDataDescription'|translate}
				<br/>
					<a href='{url module="PrivacyManager" action="privacySettings"}#deleteLogsAnchor'>
						{'PrivacyManager_ClickHereSettings'|translate:"'$clickDeleteLogSettings'"}
					</a>
				</p>
			</td>
	    </tr>
	</tbody>
</table>

<br/>

<a name="trackerDataSummary"></a>
<table class="adminTable dbstatsTable">
	<tbody>
		<tr>
			<td>
				<h2>{'DBStats_TrackerTables'|translate}</h2>
				{$trackerDataSummary}
			</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>

<a name="reportDataSummary"></a>
<table class="adminTable dbstatsTable">
	<tbody>
		<tr>
			<td>
				<h2>{'DBStats_ReportTables'|translate}</h2>
				{$reportDataSummary}
			</td>
			<td>
				<h2>{'General_Reports'|translate}</h2>
				<div class="ajaxLoad" href="index.php?module=DBStats&action=getIndividualReportsSummary&viewDataTable=table">
					<span class="loadingPiwik"><img src="themes/default/images/loading-blue.gif" />{'General_LoadingData'|translate}</span>
 				</div>
			</td>
		</tr>
	</tbody>
</table>

<a name="metricDataSummary"></a>
<table class="adminTable dbstatsTable">
	<tbody>
		<tr>
			<td>
				<h2>{'DBStats_MetricTables'|translate}</h2>
				{$metricDataSummary}
			</td>
			<td>
				<h2>{'General_Metrics'|translate}</h2>
				<div class="ajaxLoad" href="index.php?module=DBStats&action=getIndividualMetricsSummary&viewDataTable=table">
					<span class="loadingPiwik"><img src="themes/default/images/loading-blue.gif" />{'General_LoadingData'|translate}</span>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<a name="adminDataSummary"></a>
<table class="adminTable dbstatsTable">
	<tbody>
		<tr>
			<td>
				<h2>{'DBStats_OtherTables'|translate}</h2>
				{$adminDataSummary}
			</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>

{literal}
<script type="text/javascript">
(function( $ ){
	$(document).ready(function() {
		$('.ajaxLoad').each(function() {
			var self = this,
				reportUrl = $(this).attr('href');
			
			// build & execute AJAX request
			var request =
			{
				type: 'GET',
				url: reportUrl,
				dataType: 'html',
				async: true,
				error: piwikHelper.ajaxHandleError,		// Callback when the request fails
				data: {
					idSite: broadcast.getValueFromUrl('idSite'),
					period: broadcast.getValueFromUrl('period'),
					date: broadcast.getValueFromUrl('date')
				},
				success: function(data) {
					$('.loadingPiwik', self).hide();
					$(self).html(data);
				}
			};
			
			piwikHelper.queueAjaxRequest($.ajax(request));
		});
	});
})( jQuery );
</script>
{/literal}

{include file="CoreAdminHome/templates/footer.tpl"}


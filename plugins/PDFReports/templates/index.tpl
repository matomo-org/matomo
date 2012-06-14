{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}

<div class="top_controls_inner">
    {include file="CoreHome/templates/period_select.tpl"}
</div>

<div class="centerLargeDiv">
	<h2>{'PDFReports_ManageEmailReports'|translate}</h2>
	
	<div class="entityContainer">
		{ajaxErrorDiv}
		{ajaxLoadingDiv}
		{include file="PDFReports/templates/list.tpl"}
		{include file="PDFReports/templates/add.tpl"}
		<a id='bottom'></a>
	</div>
</div>

<div class="ui-confirm" id="confirm">
	<h2>{'PDFReports_AreYouSureDeleteReport'|translate}</h2>
	<input role="yes" type="button" value="{'General_Yes'|translate}" />
	<input role="no" type="button" value="{'General_No'|translate}" />
</div> 

<script type="text/javascript">
var ReportPlugin = new Object();
ReportPlugin.defaultPeriod = '{$defaultPeriod}';
ReportPlugin.defaultReportType = '{$defaultReportType}';
ReportPlugin.defaultReportFormat = '{$defaultReportFormat}';
ReportPlugin.reportList = {$reportsJSON};
ReportPlugin.createReportString = "{'PDFReports_CreateReport'|translate}";
ReportPlugin.updateReportString = "{'PDFReports_UpdateReport'|translate}";
{literal}
$(document).ready( function() {
	initManagePdf();
});
</script>
<style type="text/css">
.reportCategory {
	font-weight:bold;
	margin-bottom:5px;
}
</style>
{/literal}

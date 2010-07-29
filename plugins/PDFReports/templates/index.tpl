{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}

<div class="top_controls_inner">
    {include file="CoreHome/templates/period_select.tpl"}
</div>

<div class="centerLargeDiv">
	<h2>{'PDFReports_ManagePdfReports'|translate}</h2>
	
	<div class="entityContainer">
		{ajaxErrorDiv}
		{ajaxLoadingDiv}
		{include file="PDFReports/templates/list.tpl"}
		{include file="PDFReports/templates/add.tpl"}
		<a id='bottom'></a>
	</div>
</div>

<div class="dialog" id="confirm">
        <h2>{'PDFReports_AreYouSureYouWantToDeleteReport'|translate}</h2>
		<input id="yes" type="button" value="{'General_Yes'|translate}" />
		<input id="no" type="button" value="{'General_No'|translate}" />
</div> 

<script type="text/javascript">
piwik.PDFReports = {$reportsJSON};
piwik.updateReportString = "{'PDFReports_UpdatePDFReport'|translate}";
{literal}
$(document).ready( function() {
	initManagePdf();
});
</script>
<style>
.reportCategory {
	font-weight:bold;
	margin-bottom:5px;
}
</style>
{/literal}
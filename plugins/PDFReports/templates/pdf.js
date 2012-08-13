/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var getReportParametersFunctions = Object();
var updateReportParametersFunctions = Object();
var resetReportParametersFunctions = Object();

function formSetEditReport(idReport)
{
	var report = {
		'type' : ReportPlugin.defaultReportType,
		'format' : ReportPlugin.defaultReportFormat,
		'description' : '',
		'period' : ReportPlugin.defaultPeriod,
		'reports' : []
	};

	if(idReport > 0)
	{
		report = ReportPlugin.reportList[idReport];
		$('#report_submit').val(ReportPlugin.updateReportString);
	}
	else
	{
		$('#report_submit').val(ReportPlugin.createReportString);
	}

	toggleReportType(report.type);

	$('#report_description').html(report.description);
	$('#report_type option[value='+report.type+']').prop('selected', 'selected');
	$('#report_period option[value='+report.period+']').prop('selected', 'selected');
	$('[name=report_format].'+report.type+' option[value='+report.format+']').prop('selected', 'selected');

	$('[name=reportsList] input').prop('checked', false);

	var key;
	for(key in report.reports)
	{
		$('.' + report.type + ' [report-unique-id=' + report.reports[key] + ']').prop('checked','checked');
	}

	updateReportParametersFunctions[report.type](report.parameters);

	$('#report_idreport').val(idReport);
}

function getReportAjaxRequest(idReport, defaultApiMethod)
{
	var parameters = {};
	piwikHelper.lazyScrollTo(".entityContainer", 400);
	parameters.idSite = piwik.idSite;
	parameters.module = 'API';
	parameters.method = defaultApiMethod;
	if(idReport == 0)
	{
		parameters.method =  'PDFReports.addReport';
	}
	parameters.format = 'json';
	parameters.token_auth = piwik.token_auth;
	return parameters;
}

function toggleReportType(reportType)
{
	resetReportParametersFunctions[reportType]();
	$('#report_type option').each(function(index, type) {
		$('.'+$(type).val()).hide();
	});
	$('.'+reportType).show();
}

function initManagePdf()
{
	// Click Add/Update Submit 
	$('#addEditReport').submit( function() {
		var idReport = $('#report_idreport').val();
		var apiParameters = getReportAjaxRequest(idReport, 'PDFReports.updateReport');
		apiParameters.idReport = idReport;
		apiParameters.description = $('#report_description').val();
		apiParameters.period = $('#report_period option:selected').val();
		apiParameters.reportType = $('#report_type option:selected').val();
		apiParameters.reportFormat = $('[name=report_format].'+apiParameters.reportType+' option:selected').val();

		var reports = [];
		$('[name=reportsList].'+apiParameters.reportType+' input:checked').each(function() {
			reports.push($(this).attr('report-unique-id'));
		});
		if(reports.length > 0)
		{
			apiParameters.reports = reports;
		}

		apiParameters.parameters = getReportParametersFunctions[apiParameters.reportType]();

		var ajaxRequest = piwikHelper.getStandardAjaxConf();
		ajaxRequest.type = 'POST';
		ajaxRequest.data = apiParameters;
		$.ajax( ajaxRequest );
		return false;
	});
	
	// Email now
	$('a[name=linkSendNow]').click(function(){
		var idReport = $(this).attr('idreport');
		var ajaxRequest = piwikHelper.getStandardAjaxConf();
		ajaxRequest.type = 'POST';
		parameters = getReportAjaxRequest(idReport, 'PDFReports.sendReport');
		parameters.idReport = idReport;
		parameters.period = broadcast.getValueFromUrl('period');
		parameters.date = broadcast.getValueFromUrl('date');
		ajaxRequest.data = parameters;
		$.ajax( ajaxRequest );
	});
	
	// Delete Report
	$('a[name=linkDeleteReport]').click(function(){
		var idReport = $(this).attr('id');
		function onDelete()
		{
			var ajaxRequest = piwikHelper.getStandardAjaxConf();
			ajaxRequest.type = 'POST';
			parameters = getReportAjaxRequest(idReport, 'PDFReports.deleteReport');
			parameters.idReport = idReport;
			ajaxRequest.data = parameters;
			$.ajax( ajaxRequest );
		}
		piwikHelper.modalConfirm( '#confirm', {yes: onDelete});
	});

	// Edit Report click
	$('a[name=linkEditReport]').click(function(){
		var idReport = $(this).attr('id');
		formSetEditReport( idReport );
		$('.entityAddContainer').show();
		$('#entityEditContainer').hide();
	});

	// Switch Report Type
	$('#report_type').change(function(){
		var reportType = $(this).val();
		toggleReportType(reportType);
	});

	// Add a Report click
	$('#linkAddReport').click(function(){
		$('.entityAddContainer').show();
		$('#entityEditContainer').hide();
		formSetEditReport( idReport = 0 );
	});
	
	// Cancel click
	$('.entityCancelLink').click(function(){
		$('.entityAddContainer').hide();
		$('#entityEditContainer').show();
		piwikHelper.hideAjaxError();
	}).click();
}

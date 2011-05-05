/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function formSetEditReport(idReport)
{
	var report = { 	"description":"", 
					"period":"week",
					"email_me":"1",
					"additional_emails":"",
					"reports":[]
	};
				
	if(idReport > 0)
	{
		report = piwik.PDFReports[idReport];
	}
	$('#report_description').html(report.description);
	$('#report_period option[value='+report.period+']').attr('selected', 'selected');
	$('#report_format option[value='+report.format+']').attr('selected', 'selected');
	if(report.email_me == 1)
	{
		$('#report_email_me').attr('checked','checked');
	}
	$('#report_additional_emails').text(report.additional_emails);
	
	$('#reportsList input').attr('checked', false);

	var key;
	for(key in report.reports)
	{
		$('#'+report.reports[key]).attr('checked','checked');
	}
	$('#report_idreport').attr('value', idReport);
	$('#report_submit').attr('value', piwik.updateReportString);
}

function getPDFAjaxRequest(idReport, defaultApiMethod)
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

function initManagePdf()
{
	// Click Add/Update Submit 
	$('#addEditReport').submit( function() {
		idReport = $('#report_idreport').attr('value');
		parameters = getPDFAjaxRequest(idReport, 'PDFReports.updateReport');
		parameters.idReport = idReport;
		parameters.description = encodeURIComponent($('#report_description').val());
		parameters.period = $('#report_period option:selected').attr('value');
		parameters.reportFormat = $('#report_format option:selected').attr('value');
		parameters.emailMe = $('#report_email_me').attr('checked') == true ? 1: 0;
		additionalEmails = $('#report_additional_emails').val();
		parameters.additionalEmails = piwikHelper.getApiFormatTextarea(additionalEmails);
		reports = '';
		$('#reportsList input:checked').each(function() {
			reports += $(this).attr('id') + ',';
		});
		parameters.reports = reports;

		var ajaxRequest = piwikHelper.getStandardAjaxConf();
		ajaxRequest.type = 'POST';
		ajaxRequest.data = parameters;
		$.ajax( ajaxRequest );
		return false;
	});
	
	// Email now
	$('a[name=linkEmailNow]').click(function(){
		var idReport = $(this).attr('idreport');
		var ajaxRequest = piwikHelper.getStandardAjaxConf();
		ajaxRequest.type = 'POST';
		parameters = getPDFAjaxRequest(idReport, 'PDFReports.sendEmailReport');
		parameters.idReport = idReport;
		ajaxRequest.data = parameters;
		$.ajax( ajaxRequest );
	});
	
	// Delete PDF
	$('a[name=linkDeleteReport]').click(function(){
		var idReport = $(this).attr('id');
		function onDelete()
		{
			var ajaxRequest = piwikHelper.getStandardAjaxConf();
			ajaxRequest.type = 'POST';
			parameters = getPDFAjaxRequest(idReport, 'PDFReports.deleteReport');
			parameters.idReport = idReport;
			ajaxRequest.data = parameters;
			$.ajax( ajaxRequest );
		}
		piwikHelper.windowModal( '#confirm', onDelete);
	});

	// Edit Report click
	$('a[name=linkEditReport]').click(function(){
		var idReport = $(this).attr('id');
		formSetEditReport( idReport );
		$('.entityAddContainer').show();
		$('#entityEditContainer').hide();
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

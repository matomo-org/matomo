/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var getReportParametersFunctions = Object();
var updateReportParametersFunctions = Object();
var resetReportParametersFunctions = Object();

function formSetEditReport(idReport) {
    var report = {
        'type': ReportPlugin.defaultReportType,
        'format': ReportPlugin.defaultReportFormat,
        'description': '',
        'period': ReportPlugin.defaultPeriod,
        'hour': ReportPlugin.defaultHour,
        'reports': []
    };

    if (idReport > 0) {
        report = ReportPlugin.reportList[idReport];
        $('#report_submit').val(ReportPlugin.updateReportString);
    }
    else {
        $('#report_submit').val(ReportPlugin.createReportString);
    }

    toggleReportType(report.type);

    $('#report_description').html(report.description);
    $('#report_segment').find('option[value=' + report.idsegment + ']').prop('selected', 'selected');
    $('#report_type').find('option[value=' + report.type + ']').prop('selected', 'selected');
    $('#report_period').find('option[value=' + report.period + ']').prop('selected', 'selected');
    $('#report_hour').val(report.hour);
    $('[name=report_format].' + report.type + ' option[value=' + report.format + ']').prop('selected', 'selected');

    $('select[name=report_type]').change( toggleDisplayOptionsByFormat );
    $('select[name=report_format]').change( toggleDisplayOptionsByFormat );

    // When CSV is selected, hide "Display options"
    toggleDisplayOptionsByFormat();

    function toggleDisplayOptionsByFormat() {
        var selectorReportFormat = 'select[name=report_format].' + $('#report_type').val();
        var format = $(selectorReportFormat).val();
        var displayOptionsSelector = $('#row_report_display_options');
        if (format == 'csv' || format == 'sms') {
            displayOptionsSelector.hide();
        } else {
            displayOptionsSelector.show();
        }
    }

    $('[name=reportsList] input').prop('checked', false);

    var key;
    for (key in report.reports) {
        $('.' + report.type + ' [report-unique-id=' + report.reports[key] + ']').prop('checked', 'checked');
    }

    updateReportParametersFunctions[report.type](report.parameters);

    $('#report_idreport').val(idReport);
}

function getReportAjaxRequest(idReport, defaultApiMethod) {
    var parameters = {};
    piwikHelper.lazyScrollTo(".emailReports>h2", 400);
    parameters.module = 'API';
    parameters.method = defaultApiMethod;
    if (idReport == 0) {
        parameters.method = 'ScheduledReports.addReport';
    }
    parameters.format = 'json';
    return parameters;
}

function toggleReportType(reportType) {
    resetReportParametersFunctions[reportType]();
    $('#report_type').find('option').each(function (index, type) {
        $('.' + $(type).val()).hide();
    });
    $('.' + reportType).show();
}

function fadeInOutSuccessMessage(selector, message) {

    var UI = require('piwik/UI');
    var notification = new UI.Notification();
    notification.show(message, {
        placeat: selector,
        context: 'success',
        noclear: true,
        type: 'toast',
        style: {display: 'inline-block', marginTop: '10px'},
        id: 'usersManagerAccessUpdated'
    });

    piwikHelper.refreshAfter(2);
}

function initManagePdf() {
    // Click Add/Update Submit
    $('#addEditReport').submit(function () {
        var idReport = $('#report_idreport').val();
        var apiParameters = getReportAjaxRequest(idReport, 'ScheduledReports.updateReport');
        apiParameters.idReport = idReport;
        apiParameters.description = $('#report_description').val();
        apiParameters.idSegment = $('#report_segment').find('option:selected').val();
        apiParameters.reportType = $('#report_type').find('option:selected').val();
        apiParameters.reportFormat = $('[name=report_format].' + apiParameters.reportType + ' option:selected').val();

        var reports = [];
        $('[name=reportsList].' + apiParameters.reportType + ' input:checked').each(function () {
            reports.push($(this).attr('report-unique-id'));
        });
        if (reports.length > 0) {
            apiParameters.reports = reports;
        }

        apiParameters.parameters = getReportParametersFunctions[apiParameters.reportType]();

        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams(apiParameters, 'POST');
        ajaxHandler.addParams({period: $('#report_period').find('option:selected').val()}, 'GET');
        ajaxHandler.addParams({hour: $('#report_hour').val()}, 'GET');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement();
        if (idReport) {
            ajaxHandler.setCallback(function (response) {

                fadeInOutSuccessMessage('#reportUpdatedSuccess', _pk_translate('ScheduledReports_ReportUpdated'));
            });
        }
        ajaxHandler.send(true);
        return false;
    });

    // Email now
    $('a[name=linkSendNow]').click(function () {
        var idReport = $(this).attr('idreport');
        var parameters = getReportAjaxRequest(idReport, 'ScheduledReports.sendReport');
        parameters.idReport = idReport;
        parameters.force = true;

        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams(parameters, 'POST');
        ajaxHandler.setLoadingElement();
        ajaxHandler.setCallback(function (response) {
            fadeInOutSuccessMessage('#reportSentSuccess', _pk_translate('ScheduledReports_ReportSent'));
        });
        ajaxHandler.send(true);
    });

    // Delete Report
    $('.delete-report').click(function () {
        var idReport = $(this).attr('id');

        function onDelete() {
            var parameters = getReportAjaxRequest(idReport, 'ScheduledReports.deleteReport');
            parameters.idReport = idReport;

            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams(parameters, 'POST');
            ajaxHandler.redirectOnSuccess();
            ajaxHandler.setLoadingElement();
            ajaxHandler.send(true);
        }

        piwikHelper.modalConfirm('#confirm', {yes: onDelete});
    });

    // Edit Report click
    $('.edit-report').click(function () {
        var idReport = $(this).attr('id');
        formSetEditReport(idReport);
        $('.entityAddContainer').show();
        $('#entityEditContainer').hide();
        $(document).trigger('ScheduledReport.edit', {});
    });

    // Switch Report Type
    $('#report_type').change(function () {
        var reportType = $(this).val();
        toggleReportType(reportType);
    });

    // Add a Report click
    $('#add-report').click(function () {
        $('.entityAddContainer').show();
        $('#entityEditContainer').hide();
        formSetEditReport(/*idReport = */0);
    });

    // Cancel click
    $('.entityCancelLink').click(function () {
        $('.entityAddContainer').hide();
        $('#entityEditContainer').show();
        piwikHelper.hideAjaxError();
    }).click();
}

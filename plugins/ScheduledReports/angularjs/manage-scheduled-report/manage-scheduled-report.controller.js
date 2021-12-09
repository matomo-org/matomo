/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageScheduledReportController', ManageScheduledReportController);

    ManageScheduledReportController.$inject = ['piwik'];

    function ManageScheduledReportController(piwik) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        function getTimeZoneDifferenceInHours() {
            return piwik.timezoneOffset / 3600;
        }

        this.reportHours = [];
        for (var i = 0; i < 24; i++) {
            if ((getTimeZoneDifferenceInHours()*2) % 2 != 0) {
                this.reportHours.push({key: i + '.5', value: i + ':30'});
            } else {
                this.reportHours.push({key: i + '', value: i + ''});
            }
        }

        function scrollToTop()
        {
            piwikHelper.lazyScrollTo(".emailReports", 200);
        }

        function updateParameters(reportType, report)
        {
            if (updateReportParametersFunctions && updateReportParametersFunctions[reportType]) {
                updateReportParametersFunctions[reportType](report);
            }
        }

        function resetParameters(reportType, report)
        {
            if (resetReportParametersFunctions && resetReportParametersFunctions[reportType]) {
                resetReportParametersFunctions[reportType](report)
            }
        }

        function adjustHourToTimezone(hour, difference) {
            return '' + ((24 + parseFloat(hour) + difference) % 24);
        }

        function updateReportHourUtc (report) {
            var reportHour = adjustHourToTimezone(report.hour, -getTimeZoneDifferenceInHours());
            report.hourUtc = _pk_translate('ScheduledReports_ReportHourWithUTC', [reportHour]);
        }

        function formSetEditReport(idReport) {
            var report = {
                'type': ReportPlugin.defaultReportType,
                'format': ReportPlugin.defaultReportFormat,
                'description': '',
                'period': ReportPlugin.defaultPeriod,
                'hour': ReportPlugin.defaultHour,
                'reports': [],
                'idsegment': '',
                'evolutionPeriodFor': 'prev',
                'evolutionPeriodN': ReportPlugin.defaultEvolutionPeriodN,
                'periodParam': ReportPlugin.defaultPeriod,
            };

            if (idReport > 0) {
                report = ReportPlugin.reportList[idReport];
                updateParameters(report.type, report);
                self.saveButtonTitle = ReportPlugin.updateReportString;
            } else {
                self.saveButtonTitle = ReportPlugin.createReportString;
                resetParameters(report.type, report);
            }

            report.hour = adjustHourToTimezone(report.hour, getTimeZoneDifferenceInHours());
            updateReportHourUtc(report);

            setTimeout(function() {
              $('[name=reportsList] input').prop('checked', false);

              var key;
              for (key in report.reports) {
                  $('.' + report.type + ' [report-unique-id=' + report.reports[key] + ']').prop('checked', 'checked');
              }
            });

            report['format' + report.type] = report.format;

            if (!report.idsegment) {
                report.idsegment = '';
            }

            self.report = report;
            self.report.description = piwik.helper.htmlDecode(self.report.description);
            self.editingReportId = idReport;
        }

        function getReportAjaxRequest(idReport, defaultApiMethod) {
            scrollToTop();

            var ajaxHandler = new ajaxHelper();

            var parameters = {module: 'API', method: defaultApiMethod, format: 'json'};
            if (idReport == 0) {
                parameters.method = 'ScheduledReports.addReport';
            }

            ajaxHandler.addParams(parameters, 'GET');

            return ajaxHandler;
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
                id: 'scheduledReportSuccess'
            });

            piwikHelper.refreshAfter(2);
        }

        this.updateReportHourUtc = function () {
            updateReportHourUtc(this.report);
        };

        // Click Add/Update Submit
        this.submitReport = function () {
            var idReport = this.editingReportId;
            var apiParameters = {};
            apiParameters.idReport = idReport;
            apiParameters.description = this.report.description;
            apiParameters.idSegment = this.report.idsegment;
            apiParameters.reportType = this.report.type;
            apiParameters.reportFormat = this.report['format' + this.report.type];
            apiParameters.periodParam = this.report.periodParam;
            apiParameters.evolutionPeriodFor = this.report.evolutionPeriodFor;
            if (apiParameters.evolutionPeriodFor !== 'each') {
                apiParameters.evolutionPeriodN = this.report.evolutionPeriodN;
            }

            var period = self.report.period;
            var hour = adjustHourToTimezone(this.report.hour, -getTimeZoneDifferenceInHours());

            var reports = [];
            $('[name=reportsList].' + apiParameters.reportType + ' input:checked').each(function () {
                reports.push($(this).attr('report-unique-id'));
            });
            if (reports.length > 0) {
                apiParameters.reports = reports;
            }

            apiParameters.parameters = getReportParametersFunctions[this.report.type](this.report);

            var ajaxHandler = getReportAjaxRequest(idReport, 'ScheduledReports.updateReport');
            ajaxHandler.addParams(apiParameters, 'POST');
            ajaxHandler.addParams({period: period}, 'GET');
            ajaxHandler.addParams({hour: hour}, 'GET');
            ajaxHandler.redirectOnSuccess();
            ajaxHandler.setLoadingElement();
            if (idReport) {
                ajaxHandler.setCallback(function (response) {

                    fadeInOutSuccessMessage('#reportUpdatedSuccess', _pk_translate('ScheduledReports_ReportUpdated'));
                });
            }
            ajaxHandler.send();
            return false;
        };

        this.changedReportType = function () {
            resetParameters(this.report.type, this.report);
        };

        this.displayReport = function (reportId) {
            $('#downloadReportForm_' + reportId).submit();
        };

        // Email now
        this.sendReportNow = function (idReport) {
            var ajaxHandler = getReportAjaxRequest(idReport, 'ScheduledReports.sendReport');
            ajaxHandler.addParams({idReport: idReport, force: true}, 'POST');
            ajaxHandler.setLoadingElement();
            ajaxHandler.setCallback(function (response) {
                fadeInOutSuccessMessage('#reportSentSuccess', _pk_translate('ScheduledReports_ReportSent'));
            });
            ajaxHandler.send();
        };

        // Delete Report
        this.deleteReport = function (idReport) {
            function onDelete() {
                var ajaxHandler = getReportAjaxRequest(idReport, 'ScheduledReports.deleteReport');
                ajaxHandler.addParams({idReport: idReport}, 'POST');
                ajaxHandler.redirectOnSuccess();
                ajaxHandler.setLoadingElement();
                ajaxHandler.send();
            }

            piwikHelper.modalConfirm('#confirm', {yes: onDelete});
        };

        this.showListOfReports = function (shouldScrollToTop) {
            this.showReportsList = true;
            this.showReportForm = false;
            piwik.helper.hideAjaxError();

            if (typeof shouldScrollToTop === 'undefined' || !shouldScrollToTop) {
                scrollToTop();
            }
        };

        this.showAddEditForm = function () {
            this.showReportsList = false;
            this.showReportForm = true;
        };

        this.createReport = function () {
            this.showAddEditForm();
            formSetEditReport(/*idReport = */0);
        }

        this.editReport = function (reportId) {
            this.showAddEditForm();
            formSetEditReport(reportId);
        };

        this.getFrequencyPeriodSingle = function () {
            if (!this.report || !this.report.period) {
                return '';
            }

            var translation = ReportPlugin.periodTranslations[this.report.period];
            if (!translation) {
                translation = ReportPlugin.periodTranslations.day;
            }
            return translation.single;
        };
        this.getFrequencyPeriodPlural = function () {
            if (!this.report || !this.report.period) {
                return '';
            }

            var translation = ReportPlugin.periodTranslations[this.report.period];
            if (!translation) {
                translation = ReportPlugin.periodTranslations.day;
            }
            return translation.plural;
        };

        this.showListOfReports(false);
    }
})();

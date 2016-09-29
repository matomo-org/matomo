/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('reportingPageModel', reportingPageModelService);

    reportingPageModelService.$inject = ['$filter', 'reportingPagesModel', 'reportMetadataModel'];

    function reportingPageModelService ($filter, reportingPagesModel, reportMetadataModel) {
        var init = false;

        // those sites are going to be displayed
        var model = {
            fetchPage: fetchPage,
            resetPage: resetPage,
            widgets: [],
            page: null,
            pageContentUrl: '',
            evolutionReports: [],
            sparklineReports: []
        };

        return model;

        function resetPage()
        {
            model.page = null;
            model.widgets = [];
            model.pageContentUrl  = '';
            model.evolutionReports = [];
            model.sparklineReports = [];
        }

        function sortWidgets(widgets)
        {
            return $filter('orderBy')(widgets, 'order');
        }

        function shouldBeRenderedWithFullWidth(widget)
        {
            // rather controller logic
            if ((widget.isContainer && widget.layout && widget.layout === 'ByDimension')
                || widget.viewDataTable === 'bydimension') {
                return true;
            }

            if ('undefined' !== typeof widget.isWide && widget.isWide) {
                return true;
            }

            return widget.viewDataTable && widget.viewDataTable === 'tableAllColumns';
        }

        function buildPage(page)
        {
            if (!page) {
                return;
            }

            var widgets = [];
            var evolutionReports = [];
            var sparklineReports = [];
            var reportsToIgnore  = [];

            angular.forEach(page.widgets, function (widget) {

                if (isIgnoredReport(reportsToIgnore, widget)) {
                    return;
                }

                reportsToIgnore = reportsToIgnore.concat(getRelatedReports(widget));

                if (widget.viewDataTable && widget.viewDataTable === 'graphEvolution') {
                    evolutionReports.push(widget);
                } else if (widget.viewDataTable && widget.viewDataTable === 'sparklines') {
                    sparklineReports.push(widget);
                } else {
                    widgets.push(widget);
                }
            });

            widgets = sortWidgets(widgets);

            var groupedWidgets = [];

            if (widgets.length === 1) {
                // if there is only one widget, we always display it full width
                groupedWidgets = widgets;
            } else {
                for (var i = 0; i < widgets.length; i++) {
                    var widget = widgets[i];

                    if (shouldBeRenderedWithFullWidth(widget) || (widgets[i+1] && shouldBeRenderedWithFullWidth(widgets[i+1]))) {
                        widget.widgets = sortWidgets(widget.widgets);

                        groupedWidgets.push(widget);
                    } else {

                        var counter = 0;
                        var left = [widget];
                        var right = [];

                        while (widgets[i+1] && !shouldBeRenderedWithFullWidth(widgets[i+1])) {
                            i++;
                            counter++;
                            if (counter % 2 === 0) {
                                left.push(widgets[i]);
                            } else {
                                right.push(widgets[i]);
                            }
                        }

                        groupedWidgets.push({group: true, left: left, right: right});
                    }
                }
            }

            var copyWidgets    = angular.copy(groupedWidgets);
            var copyEvolution  = angular.copy(evolutionReports);
            var copySparklines = angular.copy(sparklineReports);

            if (copyEvolution.length) {
                copyEvolution = markWidgetsInFirstRowOfPage(copyEvolution);
            } else if (copySparklines.length) {
                copySparklines = markWidgetsInFirstRowOfPage(copySparklines);
            } else {
                copyWidgets = markWidgetsInFirstRowOfPage(copyWidgets);
            }

            // angular.copy forces the page to re-render. Otherwise it won't reload some pages
            model.evolutionReports = copyEvolution;
            model.sparklineReports = copySparklines;
            model.widgets          = copyWidgets;
        }

        function markWidgetsInFirstRowOfPage(widgets)
        {
            if (widgets && widgets[0]) {
                if (widgets[0].group) {
                    markWidgetsInFirstRowOfPage(widgets[0].left);
                    markWidgetsInFirstRowOfPage(widgets[0].right);
                } else {
                    widgets[0].isFirstInPage = true;
                }
            }

            return widgets;
        }

        function getRelatedReports(widget)
        {
            if (widget.isReport) {
                var report = reportMetadataModel.findReport(widget.module, widget.action);

                if (report && report.relatedReports) {
                    return report.relatedReports;
                }
            }

            return [];
        }

        function isIgnoredReport(reportsToIgnore, widget)
        {
            var found = false;

            if (widget.isReport) {
                angular.forEach(reportsToIgnore, function (report) {
                    if (report.module === widget.module &&
                        report.action === widget.action) {
                        found = true;
                    }
                });
            }

            return found;
        }

        function fetchPage(category, subcategory)
        {
            resetPage();

            var pagesPromise = reportingPagesModel.getAllPages();
            var reportsPromise = reportMetadataModel.fetchReportMetadata();

            return pagesPromise.then(function () {
                model.page = reportingPagesModel.findPage(category, subcategory);

                reportsPromise.then(function () {
                    buildPage(model.page);
                });

                return model.page;
            });
        }
    }
})();
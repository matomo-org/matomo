/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

var sparklineColorNames = ['backgroundColor', 'lineColor', 'minPointColor', 'maxPointColor', 'lastPointColor'];

piwik.getSparklineColors = function () {
    return piwik.ColorManager.getColors('sparkline-colors', sparklineColorNames);
};

// initializes each sparkline so they use colors defined in CSS
piwik.initSparklines = function() {
    $('.sparkline > img').each(function () {
        var $self = $(this);

        if ($self.attr('src')) {
            return;
        }

        var colors = JSON.stringify(piwik.getSparklineColors());
        var appendToSparklineUrl = '&colors=' + encodeURIComponent(colors);

        // Append the token_auth to the URL if it was set (eg. embed dashboard)
        var token_auth = broadcast.getValueFromUrl('token_auth');
        if (token_auth.length) {
            appendToSparklineUrl += '&token_auth=' + token_auth;
        }
        $self.attr('src', $self.attr('data-src') + appendToSparklineUrl);
    });
};

window.initializeSparklines = function () {
    var sparklineUrlParamsToIgnore = ['module', 'action', 'idSite', 'period', 'date', 'viewDataTable'];

    $("[data-graph-id]").each(function () {
        var graph = $(this);

        // try to find sparklines and add them clickable behaviour
        graph.parent().find('div.sparkline').each(function () {
            // find the sparkline and get it's src attribute
            var sparklineUrl = $('img', this).attr('data-src');

            if (sparklineUrl != "") {
                var params = broadcast.getValuesFromUrl(sparklineUrl);
                for (var i = 0; i != sparklineUrlParamsToIgnore.length; ++i) {
                    delete params[sparklineUrlParamsToIgnore[i]];
                }
                for (var key in params) {
                    if (typeof params[key] == 'undefined') {
                        // this happens for example with an empty segment parameter
                        delete params[key];
                    } else {
                        params[key] = decodeURIComponent(params[key]);
                    }
                }

                // on click, reload the graph with the new url
                $(this).click(function () {
                    var reportId = graph.attr('data-graph-id'),
                        dataTable = $(require('piwik/UI').DataTable.getDataTableByReport(reportId));

                    // when the metrics picker is used, the id of the data table might be updated (which is correct behavior).
                    // for example, in goal reports it might change from GoalsgetEvolutionGraph to GoalsgetEvolutionGraph1.
                    // if this happens, we can't find the graph using $('#'+idDataTable+"Chart");
                    // instead, we just use the first evolution graph we can find.
                    if (dataTable.length == 0) {
                        dataTable = $('div.dataTableVizEvolution');
                    }

                    // reload the datatable w/ a new column & scroll to the graph
                    dataTable.trigger('reload', params);
                });
            }
        });
    });
};

}(jQuery));

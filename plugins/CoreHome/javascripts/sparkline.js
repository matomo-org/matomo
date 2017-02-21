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
    var sparklineUrlParamsToIgnore = ['module', 'action', 'idSite', 'period', 'date', 'showtitle', 'viewDataTable', 'forceView', 'random'];

    $('.dataTableVizEvolution[data-report]').each(function () {
        var graph = $(this);

        // we search for .widget to make sure eg in the Dashboard to not update any graph of another report
        var selectorsToFindParent = ['.widget', '[piwik-widget-container]', '.reporting-page', 'body'];
        var index = 0, selector, parent;
        for (index; index < selectorsToFindParent.length; index++) {
            selector = selectorsToFindParent[index];
            parent = graph.parents(selector).first();
            if (parent && parent.length) {
                break;
            }
        }

        if (!parent || !parent.length) {
            return;
        }

        var sparklines = parent.find('div.sparkline:not(.notLinkable)');

        // try to find sparklines and add them clickable behaviour
        sparklines.each(function () {
            // find the sparkline and get it's src attribute
            var sparklineUrl = $('img', this).attr('data-src');

            var $this = $(this);

            if (sparklineUrl != "") {

                $this.addClass('linked');

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
                $this.off('click.sparkline');
                $this.on('click.sparkline', function () {
                    var reportId = graph.attr('data-report'),
                        dataTable = graph;

                    // when the metrics picker is used, the id of the data table might be updated (which is correct behavior).
                    // for example, in goal reports it might change from GoalsgetEvolutionGraph to GoalsgetEvolutionGraph1.
                    // if this happens, we can't find the graph using $('#'+idDataTable+"Chart");
                    // instead, we just use the first evolution graph we can find.
                    if (dataTable.length == 0) {
                        if ($(this).closest('.widget').length) {
                            dataTable = $(this).closest('.widget').find('div.dataTableVizEvolution');
                        } else {
                            dataTable = $('div.dataTableVizEvolution');
                        }
                    }

                    // reload the datatable w/ a new column & scroll to the graph
                    dataTable.trigger('reload', params);
                });
            }
        });
    });
};

}(jQuery));

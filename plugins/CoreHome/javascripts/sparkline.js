/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

var sparklineColorNames = ['backgroundColor', 'lineColor', 'minPointColor', 'maxPointColor', 'lastPointColor', 'fillColor'];

var sparklineDisplayHeight = 25;
var sparklineDisplayWidth = 100;

piwik.getSparklineColors = function () {
    var colors = piwik.ColorManager.getColors('sparkline-colors', sparklineColorNames);

    var comparisonService = window.CoreHome.ComparisonsStoreInstance;
    if (comparisonService.isComparing()) {
        var comparisons = comparisonService.getAllComparisonSeries();
        colors.lineColor = comparisons.map(function (comp) { return comp.color; });
    }

    return colors;
};

// initializes each sparkline so they use colors defined in CSS
piwik.initSparklines = function() {
    $(function () {
        $('.sparkline img').each(function () {
          var $self = $(this);

          if ($self.attr('src')) {
            return;
          }

          var seriesIndices = $self.closest('.sparkline').data('series-indices');
          var sparklineColors = piwik.getSparklineColors();

          if (seriesIndices && sparklineColors.lineColor instanceof Array) {
            sparklineColors.lineColor = sparklineColors.lineColor.filter(function (c, index) {
              return seriesIndices.indexOf(index) !== -1;
            });
          }

          var colors = JSON.stringify(sparklineColors);
          var appendToSparklineUrl = '&colors=' + encodeURIComponent(colors);

          // Append the token_auth to the URL if it was set (eg. embed dashboard)
          var token_auth = broadcast.getValueFromUrl('token_auth');
          if (token_auth.length && piwik.shouldPropagateTokenAuth) {
            appendToSparklineUrl += '&token_auth=' + token_auth;
          }
          $self.attr('width', sparklineDisplayWidth);
          $self.attr('height', sparklineDisplayHeight);
          $self.attr('src', $self.attr('data-src') + appendToSparklineUrl);
        });
    });
};

window.initializeSparklines = function () {
    $('.dataTableVizEvolution[data-report]').each(function () {
        var graph = $(this);

        // we search for .widget to make sure eg in the Dashboard to not update any graph of another report
        var selectorsToFindParent = ['.widget', '.widget-container', '.reporting-page', 'body'];
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

                var params = $this.data('graph-params') || {};
                if (!Object.keys(params).length) {
                    var urlParams = broadcast.getValuesFromUrl(sparklineUrl);

                    if (urlParams.columns) {
                        params.columns = decodeURIComponent(urlParams.columns);
                    }
                    if (urlParams.rows) {
                        params.rows = decodeURIComponent(urlParams.rows);
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

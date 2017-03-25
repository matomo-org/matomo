/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-plugin-filter>
 */
(function () {
    angular.module('piwikApp').directive('piwikPluginFilter', piwikPluginFilter);

    piwikPluginFilter.$inject = ['piwik'];

    function piwikPluginFilter(piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    updateAllNumbersOfMatchingPluginsInFilter();

                    function filterPlugins()
                    {
                        var filterOrigin = getCurrentFilterOrigin();
                        var filterStatus = getCurrentFilterStatus();

                        var $nodesToEnable = getMatchingNodes(filterOrigin, filterStatus);

                        $('#plugins tr[data-filter-origin][data-filter-status]').css('display', 'none');
                        $nodesToEnable.css('display', 'table-row');

                        updateAllNumbersOfMatchingPluginsInFilter();
                    }

                    function updateAllNumbersOfMatchingPluginsInFilter()
                    {
                        var filterOrigin = getCurrentFilterOrigin();
                        var filterStatus = getCurrentFilterStatus();

                        updateNumberOfMatchingPluginsInFilter('[data-filter-status="all"]', filterOrigin, 'all');
                        updateNumberOfMatchingPluginsInFilter('[data-filter-status="active"]', filterOrigin, 'active');
                        updateNumberOfMatchingPluginsInFilter('[data-filter-status="inactive"]', filterOrigin, 'inactive');

                        updateNumberOfMatchingPluginsInFilter('[data-filter-origin="all"]', 'all', filterStatus);
                        updateNumberOfMatchingPluginsInFilter('[data-filter-origin="core"]', 'core', filterStatus);
                        updateNumberOfMatchingPluginsInFilter('[data-filter-origin="official"]', 'official', filterStatus);
                        updateNumberOfMatchingPluginsInFilter('[data-filter-origin="thirdparty"]', 'thirdparty', filterStatus);
                    }

                    function updateNumberOfMatchingPluginsInFilter(selectorFilterToUpdate, filterOrigin, filterStatus)
                    {
                        var numMatchingNodes   = getMatchingNodes(filterOrigin, filterStatus).length;
                        var updatedCounterText = ' (' + numMatchingNodes + ')';

                        element.find(selectorFilterToUpdate + ' .counter').text(updatedCounterText);
                    }

                    function getCurrentFilterOrigin()
                    {
                        return element.find('.origin a.active').data('filter-origin');
                    }

                    function getCurrentFilterStatus()
                    {
                        return element.find('.status a.active').data('filter-status');
                    }

                    function getMatchingNodes(filterOrigin, filterStatus)
                    {
                        var query = '#plugins tr';

                        if ('all' == filterOrigin) {
                            query  += '[data-filter-origin]';
                        } else {
                            query  += '[data-filter-origin=' + filterOrigin + ']';
                        }

                        if ('all' == filterStatus) {
                            query  += '[data-filter-status]';
                        } else {
                            query  += '[data-filter-status=' + filterStatus + ']';
                        }

                        return $(query);
                    }

                    element.find('.status').on('click', 'a', function (event) {
                        event.preventDefault();

                        $(this).siblings().removeClass('active');
                        $(this).addClass('active');

                        filterPlugins();
                    });

                    element.find('.origin').on('click', 'a', function (event) {
                        event.preventDefault();

                        $(this).siblings().removeClass('active');
                        $(this).addClass('active');

                        filterPlugins();
                    });
                };
            }
        };
    }
})();
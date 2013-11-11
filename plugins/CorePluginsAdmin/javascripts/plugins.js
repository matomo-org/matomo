/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    updateNumberOfMatchingPluginsInFilter();

    function filterPlugins()
    {
        var filterOrigin = getCurrentFilterOrigin();
        var filterStatus = getCurrentFilterStatus();

        var $nodesToEnable = getMatchingNodes(filterOrigin, filterStatus);

        $('#plugins tr').css('display', 'none');
        $nodesToEnable.css('display', 'table-row');

        updateNumberOfMatchingPluginsInFilter();
    }

    function updateNumberOfMatchingPluginsInFilter()
    {
        var filterOrigin = getCurrentFilterOrigin();
        var filterStatus = getCurrentFilterStatus();

        updatePluginFilterCounter('[data-filter-status="all"]', filterOrigin, 'all');
        updatePluginFilterCounter('[data-filter-status="active"]', filterOrigin, 'active')
        updatePluginFilterCounter('[data-filter-status="inactive"]', filterOrigin, 'inactive')

        updatePluginFilterCounter('[data-filter-origin="all"]', 'all', filterStatus)
        updatePluginFilterCounter('[data-filter-origin="core"]', 'core', filterStatus)
        updatePluginFilterCounter('[data-filter-origin="noncore"]', 'noncore', filterStatus)
    }

    function updatePluginFilterCounter(query, filterOrigin, filterStatus)
    {
        var numMatchingNodes = getMatchingNodes(filterOrigin, filterStatus).length;
        $('.pluginsFilter ' + query + ' .counter').text(' (' + numMatchingNodes + ')');
    }

    function getCurrentFilterOrigin()
    {
        return $('.pluginsFilter .origin a.active').data('filter-origin');
    }

    function getCurrentFilterStatus()
    {
        return $('.pluginsFilter .status a.active').data('filter-status');
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

    $('.pluginsFilter .status').on('click', 'a', function (event) {
        event.preventDefault();

        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        filterPlugins();
    });

    $('.pluginsFilter .origin').on('click', 'a', function (event) {
        event.preventDefault();

        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        filterPlugins();
    });
});
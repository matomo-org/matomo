/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    var filterType   = 'all';
    var filterStatus = 'all';

    function filterPlugins()
    {
        var queryEnable = '#plugins tr';

        if ('all' == filterType) {
            queryEnable  += '[data-filter-type]';
        } else {
            queryEnable  += '[data-filter-type=' + filterType + ']';
        }

        if ('all' == filterStatus) {
            queryEnable  += '[data-filter-status]';
        } else {
            queryEnable  += '[data-filter-status=' + filterStatus + ']';
        }

        $('#plugins tr').css('display', 'none');
        $(queryEnable).css('display', 'table-row');
    }

    $('.pluginsFilter .status').on('click', 'a', function (event) {
        event.preventDefault();

        filterStatus = $(this).data('filter-status');
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        filterPlugins();
    });

    $('.pluginsFilter .type').on('click', 'a', function (event) {
        event.preventDefault();

        filterType = $(this).data('filter-type');
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        filterPlugins();
    });
});
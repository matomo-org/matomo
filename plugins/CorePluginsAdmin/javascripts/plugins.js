/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    function filterPlugins()
    {
        var filterStatus = $('.pluginsFilter .status a.active').data('filter-status');
        var filterOrigin = $('.pluginsFilter .origin a.active').data('filter-origin');

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

        $('#plugins tr').css('display', 'none');
        $(query).css('display', 'table-row');
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
/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(function () {
    var contentUser = $('#content.user');

    function adjustSize(content)
    {
        var width = $('body').width() - content.offset().left - 10;
        content.css('width', width + 'px');
    }

    if (contentUser.length) {
        adjustSize(contentUser);
        $(window).resize(function () {
            adjustSize(contentUser);
        });
    }

    var contentAdmin = $('#content.admin');

    if (contentAdmin.length) {
        adjustSize(contentAdmin);
        $(window).resize(function () {
            adjustSize(contentAdmin);
        });
    }
});
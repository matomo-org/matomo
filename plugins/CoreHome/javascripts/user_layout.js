/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


$(function () {
    var content = $('#content.user');
    if (!content.length) {
        return;
    }

    var width = $('body').width() - content.offset().left - 10;
    content.css('width', width + 'px');
});

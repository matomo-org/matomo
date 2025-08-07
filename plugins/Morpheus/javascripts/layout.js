/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(function () {
    function adjustSize(content)
    {
        var width = $('body').width() - content.offset().left - 16;
        content.css('width', width + 'px');
    }

    function hideAdminOnSmallViewports()
    {
        if ($(window).width() < 200 || $(window).height() < 200) {
            $('#root').css('display', 'none');
            $('#sizewarning').css('display', 'block');
        } else {
            $('#root').css('display', '');
            $('#sizewarning').css('display', 'none');
        }
    }

    var contentAdmin = $('#content.admin');

    if (contentAdmin.length) {
        adjustSize(contentAdmin);
        hideAdminOnSmallViewports()
        $(window).resize(function () {
            adjustSize(contentAdmin);
            hideAdminOnSmallViewports()
        });
    }
});

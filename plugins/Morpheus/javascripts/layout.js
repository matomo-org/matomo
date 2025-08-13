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

    var timeout = null;
    function hideAdminOnSmallViewports()
    {
        if (timeout) {
            clearTimeout(timeout);
        }
        setTimeout(function () {
            if ($(window).width() < 200 || $(window).height() < 200) {
                $('body > #root').css('display', 'none');
                $('#sizewarning').css('display', 'block');
            } else if ($('body > #root').css('display') === 'none') {
                $('body > #root').css('display', '');
                $('#sizewarning').css('display', 'none');
            }
        }, 50);
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

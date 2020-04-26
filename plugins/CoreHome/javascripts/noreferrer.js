/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
$(function () {
    if ($.browser.msie && parseInt($.browser.version) === 10) {
        $(document).on('click', 'a[rel~="noreferrer"]', function (event) {
            event.preventDefault();
            var a = event.currentTarget;
            var w = window.open(a.href, a.target || '_self');
            if (/\bnoopener\b/.test(a.rel)) {
                w.opener = null;
            }
        });
    }
});

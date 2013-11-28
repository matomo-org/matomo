/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function initTopControls() {
    if ($('#periodString').length) {
        var left=0;
        $('.top_controls').children('.js-autoLeftPanel').each(function(i, el){
            var control = $(el);
            if (left) {
                control.css({left: left});
            }
            left+=control.outerWidth(true);
        });
    }
}
/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function initTopControls() {
    var $topControlsContainer = $('.top_controls'),
        left = 0;

    if ($topControlsContainer.length) {
        $('.piwikTopControl').each(function () {
            var $control = $(this);
            if ($control.css('display') == 'none') {
                return;
            }

            $control.css('left', left);

            if (!$.contains($topControlsContainer[0], this)) {
                $control.detach().appendTo($topControlsContainer);
            }

            left += $control.outerWidth(true);
        });
    }
}
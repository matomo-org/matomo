/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    Mousetrap.bind('d', function(event) {
        if (event.altKey) {
            return;
        }
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false; // IE
        }
        $('#periodString .title').trigger('click').focus();
    });

}(jQuery));

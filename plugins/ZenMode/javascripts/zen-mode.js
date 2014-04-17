/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    if (!$('.Menu--dashboard').length) {
        return;
    }

    var addedElement = $('#topRightBar').append(
          '<span class="topBarElem activateZenMode" piwik-zen-mode-switcher>'
        + '<img src="plugins/CoreHome/images/navigation_expand.png">'
        + ' </span>'
    );

    piwikHelper.compileAngularComponents(addedElement);

    addedElement = $('.Menu--dashboard').prepend(
          '<span piwik-zen-mode-switcher class="deactivateZenMode">'
        + '<img src="plugins/CoreHome/images/navigation_collapse.png" >'
        + '</span>');

    piwikHelper.compileAngularComponents(addedElement);
});

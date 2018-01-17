/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
var tourEngagement = {
    skipStep: function (key) {
        var $step = $('.tourEngagement .' + key + ' ');
        $step.find('.icon-hide').removeClass('icon-hide').addClass('icon-ok');

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({module: 'Tour', action: 'skipstep', key: key}, 'get');
        ajaxRequest.withTokenInUrl();
        ajaxRequest.setFormat('html');
        ajaxRequest.send();
    }
};
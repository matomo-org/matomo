/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var tourEngagement = {
    skipChallenge: function (key) {
        var $challenge = $('.tourEngagement .' + key + ' ');
        $challenge.find('.icon-hide').removeClass('icon-hide').addClass('icon-ok');

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({module: 'API', method: 'Tour.skipChallenge', id: key, format: 'json'}, 'get');
        ajaxRequest.withTokenInUrl();
        ajaxRequest.setFormat('json');
        ajaxRequest.send();
    },
    goToPage: function (page) {

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({module: 'Tour', action: 'getEngagement', page: page}, 'get');
        ajaxRequest.withTokenInUrl();
        ajaxRequest.setFormat('html');
        ajaxRequest.setCallback(function (callback) {
            $('.widgetBody.tourEngagement').parent().html(callback);
        })
        ajaxRequest.send();
    }
};

/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.service').service('piwikAccess', function(piwikApi){

    this.hasSuperUserAccess = false;

    this.fetchHasSuperUserAccess = function () {
        var that = this;

        piwikApi.fetch({method: 'UsersManager.hasSuperUserAccess'}).then(function (response) {
            if (response && response.value) {
                that.hasSuperUserAccess = response.value;
            }
            return response;
        });
    }
});
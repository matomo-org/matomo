/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.service').service('websiteGroups', function(piwikApi){

    this.groups = [];

    this.fetchSitesGroups = function () {
        var that = this;
        piwikApi.fetch({method: 'SitesManager.getSitesGroups'}).then(function (response) {
            if (response && response.length) {
                var indexEmpty = response.indexOf('');
                if (-1 != indexEmpty) {
                    response.splice(indexEmpty, 1);
                }

                that.groups = response;
            }
        });
    }

    this.addGroup = function (groupname) {
        if (groupname && -1 === this.groups.indexOf(groupname)) {
            this.groups.push(groupname);
        }
    }

    this.assignGroup = function (website, groupName) {
        piwikApi.post({
            method: 'SitesManager.updateSite',
            idSite: website.idsite,
            group: groupName
        }).then(function () {
            website.group = groupName;
        });
    }
});
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

                response.sort();
                that.groups = response;
            }
        });
    }

    this.addGroup = function (groupname) {
        if (groupname && -1 === this.groups.indexOf(groupname)) {
            this.groups.push(groupname);
        }
    }

    this.assignGroup = function (idSite, groupName) {
        return piwikApi.post({
            method: 'SitesManager.updateSite',
            idSite: idSite,
            group: groupName
        });
    }

    this.renameGroup = function (oldGroupName, newGroupName) {
        return piwikApi.post({
            method: 'SitesManager.renameGroup',
            oldGroupName: oldGroupName,
            newGroupName: newGroupName
        });
    }
});
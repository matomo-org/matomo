/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Filters a given list of websites and groups and makes sure only the websites within a given offset and limit are
 * displayed. It also makes sure sites are displayed under the groups. That means it flattens a structure like this:
 *
 * - website1
 * - website2
 * - website3.sites // this is a group
 *    - website4
 *    - website5
 * - website6
 *
 * to the following structure
 * - website1
 * - website2
 * - website3.sites // this is a group
 * - website4
 * - website5
 * - website6
 */
(function () {
    angular.module('piwikApp').filter('multiSitesGroupFilter', multiSitesGroupFilter);

    function multiSitesGroupFilter() {
        return function(websites, from, to) {
            var offsetEnd = parseInt(from, 10) + parseInt(to, 10);
            var groups    = {};

            var sites = [];
            for (var index = 0; index < websites.length; index++) {
                var website = websites[index];

                sites.push(website);
                if (website.sites && website.sites.length) {
                    groups[website.label] = website;
                    for (var innerIndex = 0; innerIndex < website.sites.length; innerIndex++) {
                        sites.push(website.sites[innerIndex]);
                    }
                }

                if (sites.length >= offsetEnd) {
                    break;
                }
            }

            // if the first site is a website having a group, then try to find the related group and prepend it to the list
            // of sites to make sure we always display the name of the group that belongs to a website.
            var filteredSites = sites.slice(from, offsetEnd);

            if (filteredSites.length && filteredSites[0] && filteredSites[0].group) {
                var groupName = filteredSites[0].group;
                if (groups[groupName]) {
                    filteredSites.unshift(groups[groupName]);
                }
            }

            return filteredSites;
        };
    }
})();


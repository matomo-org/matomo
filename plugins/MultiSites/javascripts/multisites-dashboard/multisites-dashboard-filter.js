/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').filter('multiSitesGroupFilter', function() {
    return function(websites, from, to) {
        var offsetEnd = parseInt(from, 10) + parseInt(to, 10);

        var sites = [];
        for (var index = 0; index < websites.length; index++) {
            var website = websites[index];

            sites.push(website);
            if (website.sites && website.sites.length) {
                for (var innerIndex = 0; innerIndex < website.sites.length; innerIndex++) {
                    sites.push(website.sites[innerIndex]);
                }
            }

            if (sites.length >= offsetEnd) {
                break;
            }
        }

        return sites.slice(from, to);
    }
});


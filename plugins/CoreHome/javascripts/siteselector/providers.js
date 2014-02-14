
piwikApp.factory('siteSelectorModel', function (piwikApi, $filter) {
    var filterLimit = 10;

    var model = {};
    model.sites = [];
    model.hasMultipleWebsites = false;
    model.isLoading = false;
    model.firstSiteName = '';

    model.updateWebsitesList = function (websites) {

        if (!websites || !websites.length) {
            model.sites = [];
            return;
        }

        angular.forEach(websites, function (website) {
            website.name = $filter('htmldecode')(website.name);
        });

        model.sites = websites;

        if (!model.firstSiteName) {
            model.firstSiteName = websites[0].name;
        }

        model.hasMultipleWebsites = model.hasMultipleWebsites || websites.length > 1;
    };

    model.searchSite = function (term) {
        if (!term) {
            model.loadInitialSites();
            return;
        }
        model.isLoading = true;
        piwikApi.fetch({
            method: 'SitesManager.getPatternMatchSites',
            filter_limit: filterLimit,
            pattern: term
        }).then(function (response) {
            model.updateWebsitesList(response);
        }).finally(function () {
            model.isLoading = false;
        });
    };

    model.loadInitialSites = function () {
        model.isLoading = true;
        piwikApi.fetch({
            method: 'SitesManager.getSitesWithAtLeastViewAccess',
            filter_limit: filterLimit,
            showColumns: 'name,idsite'
        }).then(function (response) {
            model.updateWebsitesList(response);
        }).finally(function () {
            model.isLoading = false;
        });
    }

    return model;
});

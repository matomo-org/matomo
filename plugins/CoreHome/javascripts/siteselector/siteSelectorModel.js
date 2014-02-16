
piwikApp.factory('siteSelectorModel', function (piwikApi, $filter) {

    var model = {};
    model.sites = [];
    model.hasMultipleWebsites = false;
    model.isLoading = false;
    model.firstSiteName = '';

    function fetchAndUpdate(params)
    {
        if (model.isLoading) {
            piwikApi.abort();
        }

        model.isLoading = true;

        params.filter_limit = 10;
        params.showColumns  = 'name,idsite';

        piwikApi.fetch(params).then(function (response) {
            model.updateWebsitesList(response);
        }).finally(function () {
            model.isLoading = false;
        });
    }

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

        fetchAndUpdate({
            method: 'SitesManager.getPatternMatchSites',
            pattern: term
        });
    };

    model.loadInitialSites = function () {
        fetchAndUpdate({
            method: 'SitesManager.getSitesWithAtLeastViewAccess'
        })
    }

    return model;
});

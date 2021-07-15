/**
 * Model for Sites Manager. Fetches only sites one has at least Admin permission.
 */

var sitesManagerAdminSitesModel = {

    sites: [],
    searchTerm: '',
    isLoading: false,
    pageSize: 10,
    currentPage: 0,
    offsetStart: 0,
    offsetEnd: 10,
    hasPrev: false,
    hasNext: false,

    onError() {
        this.setSites([]);
    },

    setSites(sites) {
        this.sites = sites;

        var numSites = sites.length;
        this.offsetStart = this.currentPage * this.pageSize + 1;
        this.offsetEnd = this.offsetStart + numSites - 1;
        this.hasPrev = this.currentPage >= 1;
        this.hasNext = numSites === this.pageSize;
    },

    setCurrentPage(page) {
        if (page < 0) {
            page = 0;
        }

        this.currentPage = page;
    },

    previousPage() {
        this.setCurrentPage(this.currentPage - 1);
        this.fetchLimitedSitesWithAdminAccess();
    },

    nextPage() {
        this.setCurrentPage(this.currentPage + 1);
        this.fetchLimitedSitesWithAdminAccess();
    },

    searchSite(term) {
        this.searchTerm = term;
        this.setCurrentPage(0);
        this.fetchLimitedSitesWithAdminAccess();
    },

    fetchLimitedSitesWithAdminAccess(callback) {
        var piwikApi = piwikHelper.getAngularDependency('piwikApi');

        if (this.isLoading) {
            piwikApi.abort();
        }

        this.isLoading = true;

        var limit = this.pageSize;
        var offset = this.currentPage * this.pageSize;

        var params = {
            method: 'SitesManager.getSitesWithAdminAccess',
            fetchAliasUrls: true,
            limit: limit + offset, // this is applied in SitesManager.getSitesWithAdminAccess API
            filter_offset: offset, // filter_offset and filter_limit is applied in response builder
            filter_limit: limit
        };

        if (this.searchTerm) {
            params.pattern = this.searchTerm;
        }

        var self = this;

        return piwikApi.fetch(params).then(function (sites) {

            if (!sites) {
                self.onError();
                return;
            }

            self.setSites(sites);

        }, this.onError).finally(function () {
            if (callback) {
                callback();
            }

            self.isLoading = false;
        });
    }

};

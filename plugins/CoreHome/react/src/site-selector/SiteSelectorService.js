import matomoApiService from '../common/MatomoApi';

export class SiteSelectorService {
    constructor({ onlySitesWithAdminAccess }) {
        this.initialSites = null;
        this.numWebsitesToDisplayPerPage = null;
        this.onlySitesWithAdminAccess = onlySitesWithAdminAccess;
    }

    async loadInitialSites() {
        if (this.initialSites) {
            return this.initialSites;
        }

        const sites = await this.searchSite('%');
        this.initialSites = sites;
        return sites;
    }

    // TODO: request aborting not implemented
    async searchSite(term) {
        if (!term) {
            return await this.loadInitialSites();
        }

        const limit = await this.getNumWebsitesToDisplayPerPage();
        const methodToCall = this.onlySitesWithAdminAccess ? 'SitesManager.getSitesWithAdminAccess' : 'SitesManager.getPatternMatchSites';

        let result = await matomoApiService.fetch({
            method: methodToCall,
            limit: limit,
            pattern: term,
        });

        if (!result || !result.length) {
            result = [];
        }

        result.forEach(site => {
            if (site.group) site.name = '[' + site.group + '] ' + site.name;
        })

        result.sort((lhs, rhs) => {
            if (lhs < rhs) {
                return -1;
            }
            return lhs > rhs ? 1 : 0;
        });

        return result;
    }

    async getNumWebsitesToDisplayPerPage() {
        if (this.numWebsitesToDisplayPerPage !== null) {
            return this.numWebsitesToDisplayPerPage;
        }

        const result = await matomoApiService.fetch({ method: 'SitesManager.getNumWebsitesToDisplayPerPage' });
        this.numWebsitesToDisplayPerPage = result.value;
        return result.value;
    }
}

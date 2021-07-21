import axios from 'axios';

export class MatomoApi {
    fetch(params) {
        const body = new URLSearchParams({
            token_auth: piwik.token_auth,
            force_api_session: piwik.broadcast.isWidgetizeRequestWithoutSession() ? '0' : '1',
        }).toString();

        const apiParams = {
            module: 'API',
            action: 'index',
            format: 'JSON',
        };

        const paramsThatCanOverride = ['idSite', 'period', 'date', 'segment', 'comparePeriods', 'compareDates'];

        const mergedParams = Object.assign({}, this.getCurrentUrlParams(paramsThatCanOverride),
            this.getCurrentHashParams(paramsThatCanOverride), apiParams, params);
        const query = new URLSearchParams(mergedParams).toString();

        const headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            // ie 8,9,10 caches ajax requests, prevent this
            'cache-control': 'no-cache'
        };

        return axios.post('index.php?' + query, body, {
            headers,
        });
    }

    getCurrentUrlParams(paramsThatCanOverride) {
        return this.getSomeUrlParams(window.location.search, paramsThatCanOverride);
    }

    getCurrentHashParams(paramsThatCanOverride) {
        return this.getSomeUrlParams(window.location.hash.replace(/^[\/#?]/g, ''), paramsThatCanOverride);
    }

    // TODO: may not handle array params correctly
    getSomeUrlParams(search, paramsThatCanOverride) {
        const params = new URLSearchParams(search);
        const result = {};
        paramsThatCanOverride.forEach(param => result[param] = params.get(param))
        return result;
    }
}

const matomoApiService = new MatomoApi();

export default matomoApiService;
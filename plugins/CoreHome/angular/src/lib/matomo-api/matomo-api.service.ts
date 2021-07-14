import {Injectable} from "@angular/core";
import {Observable} from "rxjs";
import {HttpClient} from "@angular/common/http";

const piwik: any = (window as any).piwik;

@Injectable()
export class MatomoApiService {
    constructor(private http: HttpClient) {}

    fetch<T>(params: { [name: string]: string|number }): Observable<T> {
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

        return this.http.post('index.php?' + query, body, {
            headers,
        }) as Observable<T>;
    }

    private getCurrentUrlParams(paramsThatCanOverride: string[]) {
        return this.getSomeUrlParams(window.location.search, paramsThatCanOverride);
    }

    private getCurrentHashParams(paramsThatCanOverride: string[]) {
        return this.getSomeUrlParams(window.location.hash.replace(/^[\/#?]/g, ''), paramsThatCanOverride);
    }

    // TODO: may not handle array params correctly
    private getSomeUrlParams(search: string, paramsThatCanOverride: string[]) {
        const params = new URLSearchParams(search);
        const result: {[name: string]: string|null} = {};
        paramsThatCanOverride.forEach(param => result[param] = params.get(param))
        return result;
    }
}

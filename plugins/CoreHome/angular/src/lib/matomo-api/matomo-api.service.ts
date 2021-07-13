import {Injectable} from "@angular/core";
import {Observable} from "rxjs";
import {HttpClient} from "@angular/common/http";

const piwik: any = (window as any).piwik;

@Injectable()
export class MatomoApiService {
    constructor(private http: HttpClient) {}

    fetch<T>(params: { [name: string]: string|number }): Observable<T> {
        const body = {
            token_auth: piwik.token_auth,
            force_api_session: piwik.broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1,
        };

        const mergedParams = Object.assign(this.getCurrentUrlParams(), this.getCurrentHashParams(), params);
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

    private getCurrentUrlParams() {
        return new URLSearchParams(window.location.search);
    }

    private getCurrentHashParams() {
        return new URLSearchParams(window.location.hash.replace(/^\//g, ''));
    }
}

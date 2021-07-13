import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Site } from './site';
import {filter, map, shareReplay, switchMap} from "rxjs/operators";
import {MatomoApiService} from "../matomo-api/matomo-api.service";

// NOTE: all public methods return Observables. all functions used w/ map() are pure, they return new objects/arrays to avoid
// side effects.

@Injectable()
export class SitesService {
    constructor(private _matomoApi: MatomoApiService) {}

    public onlySitesWithAdminAccess: boolean = true;

    // TODO: this should technically be in a global display options service
    private readonly _numWebsitesToDisplayPerPage$ = this._matomoApi.fetch<any>({ method: 'SitesManager.getNumWebsitesToDisplayPerPage' })
        .pipe(
            map(response => parseInt(response.value)),
            shareReplay(1),
        );

    private initialSites$?: Observable<Site[]>;

    getNumWebsitesToDisplayPerPage() {
        return this._numWebsitesToDisplayPerPage$;
    }

    public loadInitialSites() {
        if (!this.initialSites$) {
            this.initialSites$ = this.searchSites('%').pipe(shareReplay(1));
        }
        return this.initialSites$;
    }

    searchSites(term: string): Observable<Site[]> {
        if (!term) {
            return this.loadInitialSites();
        }

        let methodToCall = 'SitesManager.getPatternMatchSites';
        if (this.onlySitesWithAdminAccess) {
            methodToCall = 'SitesManager.getSitesWithAdminAccess';
        }

        return this._numWebsitesToDisplayPerPage$.pipe(
            switchMap(limit => this._matomoApi.fetch<Site[]>({
                method: methodToCall,
                limit: limit,
                pattern: term,
            })),
            filter(response => response instanceof Array),
            map(sites => this._sortSites(sites)),
            map(sites => this._enrichSites(sites)),
        );
    }

    private _enrichSites(sites: Site[]): Site[] {
        return sites.map(site => {
            if (!site.group) {
                return site;
            }

            return Object.assign({}, site, { name: `[${site.group}] ${site.name}` });
        });
    }

    private _sortSites(sites: Site[]): Site[] {
        return sites.concat([]).sort((lhs, rhs) => {
            if (lhs < rhs) {
                return -1;
            }
            return lhs > rhs ? 1 : 0;
        });
        return sites;
    }
}
import {Component, ElementRef, Input, OnInit, Type, ViewChild} from '@angular/core';
import {downgradeComponent, UpgradeModule} from '@angular/upgrade/static';
import {Site} from "../site-store/site";
import {BehaviorSubject, Observable} from "rxjs";
import {map} from "rxjs/operators";
import {SitesService} from "../site-store/sites.service";

// TODO: put this in a types file
declare var _pk_translate : (v: string) => string;

@Component({
    selector: 'site-selector',
    templateUrl: './siteselector.component.html',
    styles: [
    ]
})
export class SiteSelectorComponent implements OnInit {
    constructor(private sitesService: SitesService) {}

    @Input() showSelectedSite: boolean = false;
    @Input() showAllSitesItem: boolean = true;
    @Input() switchSiteOnSelect: boolean = true;
    @Input() onlySitesWithAdminAccess: boolean = false;
    @Input() name: string = '';
    @Input() allSitesText: string = _pk_translate('General_MultiSitesSummary');
    @Input() allSitesLocation: string = 'bottom';
    @Input() placeholder: string = '';

    showSitesList: boolean = false;

    private _sitesSubject: BehaviorSubject<Site[]> = new BehaviorSubject<Site[]>([]);

    readonly _sites$: Observable<Site[]> = this._sitesSubject.asObservable();
    readonly _hasMultipleWebsites$ = this._sites$.pipe(map(sites => sites.length > 1));
    readonly _hasOnlyOneSite$ = this._hasMultipleWebsites$.pipe(map(x => !x));

    ngOnInit(): void {
        // TODO
    }
}

/*
    private readonly _sitesSource = new BehaviorSubject<Site[]>([]);
    readonly sites$ = this._sitesSource.asObservable();
    readonly firstSiteName$ = this.sites$.pipe(map(sites => sites?.[0].name));

inputName: '@name',
allSitesText: '@',
allSitesLocation: '@',
placeholder: '@'

            name: '',
            siteid: piwik.idSite,
            sitename: piwik.helper.htmlDecode(piwik.siteName),
            allSitesLocation: 'bottom',

*/
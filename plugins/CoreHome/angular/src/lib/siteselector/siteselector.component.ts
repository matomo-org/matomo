import {
    Component,
    ElementRef,
    EventEmitter,
    Input,
    OnInit,
    Output,
    QueryList,
    SecurityContext,
    SimpleChanges,
    ViewChild,
    ViewChildren
} from '@angular/core';
import {Site} from "../site-store/site";
import {BehaviorSubject, Observable} from "rxjs";
import {combineLatest, filter, map} from "rxjs/operators";
import {SitesService} from "../site-store/sites.service";
import {DomSanitizer} from "@angular/platform-browser";

// TODO: put this in a types file
declare var _pk_translate : (v: string) => string;

const piwik: any = (window as any).piwik;
const piwikHelper: any = (window as any).piwikHelper;

@Component({
    selector: 'site-selector-all-sites-link',
    template: `
        <div
            (click)="onClickLink.next($event)"
            class="custom_select_all"
        >
            <a 
                [attr.href]="getUrlAllSites()"
                (click)="$event.preventDefault()"
                tabindex="4"
                [innerHTML]="allSitesText"
            >
            </a>
        </div>
    `,
})
export class SiteSelectorAllSitesLink {
    @Input() allSitesText: string = '';
    @Output() onClickLink: EventEmitter<MouseEvent> = new EventEmitter<MouseEvent>();

    getUrlAllSites() {
        const newParameters = 'module=MultiSites&action=index';
        return (window as any).piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
    }
}

interface SiteRef {
    id: string|number;
    name: string;
}

@Component({
    selector: 'piwik-siteselector',
    template: `<div #rootdiv
                    focusAnywhereButHere
                    (onLoseFocus)="showSitesList=false"
                    class="siteSelector piwikSelector borderedControl"
                    [class.expanded]="showSitesList"
                    [class.disabled]="_hasOnlyOneSite$ | async"
>
        <input *ngIf="name" type="hidden" [attr.name]="name" [value]="selectedSite?.id"/>

        <a
                #selectedSiteDisplay
                (click)="onClickSelector()"
                (keyup.enter)="onClickSelector()"
                href="javascript:void(0)"
                [attr.title]="(_hasMultipleWebsites$ | async) ? ('CoreHome_ChangeCurrentWebsite'|translate) : ''"
                class="title"
                [class.loading]="isLoading"
                tabindex="4"
        >
            <span
                class="icon icon-arrow-bottom"
                [class.iconHidden]="isLoading"
                [class.collapsed]="!showSitesList"
            >
            </span>
            <span *ngIf="selectedSite?.name || !placeholder">{{ selectedSite?.name || (_firstSiteName$|async) }}</span>
            <span *ngIf="!selectedSite?.name && placeholder">{{ placeholder }}</span>
        </a>

        <div *ngIf="showSitesList" class="dropdown">
            <div class="custom_select_search" *ngIf="((autocompleteMinSites$|async) || 0) <= ((_sitesLength$|async) || 0) || searchTerm">
                <input
                        #customSelectInput
                        type="text"
                        (click)="searchTerm=''"
                        [(ngModel)]="searchTerm"
                        (change)="searchSite()"
                        [attr.placeholder]="'General_Search'|translate"
                        tabindex="4"
                        class="websiteSearch inp browser-default"
                />
                <!-- TODO: translate Clear? -->
                <img title="Clear"
                     *ngIf="searchTerm"
                     (click)="clearSearchTerm()"
                     class="reset"
                     src="plugins/CoreHome/images/reset_search.png"
                />
            </div>

            <div *ngIf="allSitesLocation=='top' && showAllSitesItem">
                <site-selector-all-sites-link
                        [allSitesText]="allSitesText"
                        (onClickLink)="onClickAllSitesLink($event)"
                >
                </site-selector-all-sites-link>
            </div>

            <div class="custom_select_container">
                <ul class="custom_select_ul_list" (click)="showSitesList=false">
                    <!-- !showSelectedSite && activeSiteId==site.idsite -->
                    <li
                            *ngFor="let site of (_sites$|async)"
                            (click)="switchSite(site, $event)"
                            [hidden]="!showSelectedSite && activeSiteId == site.idsite"
                    >
                        <a
                                #listLink
                                (click)="$event.preventDefault()"
                                [attr.href]="getUrlForSiteId(site.idsite)"
                                [attr.title]="site.name"
                                [innerHTML]="site.name"
                                tabindex="4"
                        >
                        </a>
                    </li>
                </ul>

                <ul
                        *ngIf="!(_sitesLength$ | async) && searchTerm"
                        class="ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all siteSelect"
                >
                    <li class="ui-menu-item">
                        <a class="ui-corner-all" tabindex="-1">{{ ('SitesManager_NotFound'|translate) + ' ' + searchTerm }}</a>
                    </li>
                </ul>
            </div>

            <div
                    *ngIf="allSitesLocation=='bottom' && showAllSitesItem"
            >
                <site-selector-all-sites-link
                        [allSitesText]="allSitesText"
                        (onClickLink)="onClickAllSitesLink($event)"
                >
                </site-selector-all-sites-link>
            </div>
        </div>
    </div>
    `,
})
export class SiteSelectorComponent implements OnInit {
    constructor(private sitesService: SitesService, private sanitizer: DomSanitizer) {}

    @Input() showSelectedSite: boolean = false;
    @Input() showAllSitesItem: boolean = true;
    @Input() switchSiteOnSelect: boolean = true;
    @Input() onlySitesWithAdminAccess: boolean = false;
    @Input() name: string = '';
    @Input() allSitesText: string = _pk_translate('General_MultiSitesSummary');
    @Input() allSitesLocation: string = 'bottom';
    @Input() placeholder: string = '';
    @Input() siteid?: string;
    @Input() sitename?: string;
    @Output() onSelectedSiteChange: EventEmitter<SiteRef> = new EventEmitter<SiteRef>();

    showSitesList: boolean = false;
    selectedSite?: SiteRef;
    isLoading: boolean = false;
    searchTerm: string = '';
    readonly activeSiteId: number|string = (window as any).piwik.idSite;
    private _firstLoad: boolean = true;

    private _sitesSubject: BehaviorSubject<Site[]> = new BehaviorSubject<Site[]>([]);
    private _changes$: BehaviorSubject<SimpleChanges> = new BehaviorSubject<SimpleChanges>({});

    readonly autocompleteMinSites$ = this.sitesService.getNumWebsitesToDisplayPerPage();

    readonly _sites$: Observable<Site[]> = this._sitesSubject.asObservable();
    readonly _hasMultipleWebsites$ = this._sites$.pipe(map(sites => sites.length > 1));
    readonly _hasOnlyOneSite$ = this._hasMultipleWebsites$.pipe(map(x => !x));
    readonly _firstSiteName$ = this._sites$.pipe(map(x => x?.[0]?.name));
    readonly _sitesLength$ = this._sites$.pipe(map(x => x.length));

    @ViewChild('customSelectInput') _customSelectInput?: ElementRef;
    @ViewChild('selectedSiteDisplay') _selectedSiteDisplay?: ElementRef;
    @ViewChildren('listLink') _siteLinks?: QueryList<ElementRef>;

    onClickSelector() {
        this.showSitesList = !this.showSitesList;
        if (!this.showSitesList) {
            return;
        }

        if (this.isLoading) {
            return;
        }

        this._loadInitialSites();
    }

    ngOnInit() {
        this._setInitialSelectedSite();
        this._onShowingSiteListGrabFocus();
        this._onSearchTermChangeHighlightSiteList();
        this._registerShortcuts();
        this._loadInitialSites();
    }

    _setInitialSelectedSite() {
        if (this.siteid && this.sitename) {
            this.selectedSite = {id: this.siteid, name: this.sitename};
        }
    }

    _onShowingSiteListGrabFocus() {
        // NOTE: equivalent of a $watch on the expression in site selector
        this._changes$.pipe(
            combineLatest(this.autocompleteMinSites$, this._sitesLength$),
            map(([changes, autoCompleteMinSites, sitesLength]) => this.showSitesList && (autoCompleteMinSites <= sitesLength || this.searchTerm)),
        ).subscribe(autoFocus => {
            if (autoFocus) {
                this._customSelectInput?.nativeElement?.focus()
            }
        });
    }

    _onSearchTermChangeHighlightSiteList() {
        this._changes$.pipe(
            filter(changes => !! changes.searchTerm),
            map(changes => changes.searchTerm.currentValue),
        ).subscribe(newSearchTermValue => {
            if (!newSearchTermValue) {
                return;
            }

            this._siteLinks?.forEach(({ nativeElement }: ElementRef) => {
                let content = piwikHelper.htmlEntities(nativeElement.textContent);
                const startTerm = content.toLowerCase().indexOf(newSearchTermValue.toLowerCase());

                if (-1 !== startTerm) {
                    const word = content.substring(startTerm, newSearchTermValue.length);
                    const escapedWord = this.sanitizer.sanitize(SecurityContext.NONE, word);
                    content = content.replace(word, '<span class="autocompleteMatched">' + escapedWord + '</span>');
                    nativeElement.innerHTML = content;
                }
            });
        });
    }

    ngOnChanges(changes: SimpleChanges) {
        this._changes$.next(changes);
    }

    searchSite() {
        this.sitesService.searchSites(this.searchTerm).subscribe(sites => this._sitesSubject.next(sites));
    }

    clearSearchTerm() {
        this.searchTerm = '';
        this._loadInitialSites();
    }

    private _loadInitialSites() {
        this.sitesService.loadInitialSites().subscribe(sites => {
            if (this._firstLoad) {
                this._selectInitialSite(sites);
            }

            this._firstLoad = false;
            this._sitesSubject.next(sites);
        });
    }

    private _selectInitialSite(sites: Site[]) {
        if (!this.selectedSite && sites.length == 1) {
            this.selectedSite = {id: sites[0].idsite, name: sites[0].name};
        }
    }

    onClickAllSitesLink(event: MouseEvent) {
        this.switchSite({idsite: 'all', name: this.allSitesText}, event);
        this.showSitesList = false;
    }

    switchSite(switchToSite: { idsite: string|number; name: string }, event: MouseEvent) {
        // for Mac OS cmd key needs to be pressed, ctrl key on other systems
        const controlKey = navigator.userAgent.indexOf("Mac OS X") !== -1 ? event.metaKey : event.ctrlKey;

        if (event && controlKey && (event.target as any)?.href) {
            window.open((event.target as any).href, "_blank");
            return;
        }

        this.selectedSite = {id: switchToSite.idsite, name: switchToSite.name};

        const activeSiteId: string|number = (window as any).piwik.idSite;
        if (!this.switchSiteOnSelect || activeSiteId == switchToSite.idsite) {
            this.onSelectedSiteChange.emit(this.selectedSite);
            return;
        }

        this.loadSite(switchToSite.idsite);
    }

    loadSite(idSite: string|number) {
        if (idSite == 'all') {
            document.location.href = piwikHelper.getCurrentQueryStringWithParametersModified(piwikHelper.getQueryStringFromParameters({
                module: 'MultiSites',
                action: 'index',
                date: piwik.currentDateString,
                period: piwik.period,
            }));
        } else {
            piwik.broadcast.propagateNewPage('segment=&idSite=' + idSite, false);
        }
    }

    getUrlForSiteId(idSite: string|number) {
        const idSiteParam   = 'idSite=' + idSite;
        const newParameters = 'segment=&' + idSiteParam;
        const hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) +
            '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    }

    private _registerShortcuts() {
        piwikHelper.registerShortcut('w', _pk_translate('CoreHome_ShortcutWebsiteSelector'), (event: KeyboardEvent) => {
            if (event.altKey) {
                return;
            }
            if (event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false; // IE
            }

            this.onClickSelector();
            this._selectedSiteDisplay?.nativeElement.focus();
        });
    }
}

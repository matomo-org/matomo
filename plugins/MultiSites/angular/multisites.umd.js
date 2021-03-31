(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/common/http'), require('@angular/router'), require('@angular/common'), require('@angular/forms'), require('core-home'), require('@angular/platform-browser')) :
    typeof define === 'function' && define.amd ? define('multisites', ['exports', '@angular/core', '@angular/common/http', '@angular/router', '@angular/common', '@angular/forms', 'core-home', '@angular/platform-browser'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.multisites = {}, global.ng.core, global.ng.common.http, global.ng.router, global.ng.common, global.ng.forms, global.i4, global.ng.platformBrowser));
}(this, (function (exports, i0, i1, i2, i2$1, i3, i4, platformBrowser) { 'use strict';

    /**
     * export globalValues
     */
    var GlobalConstants = /** @class */ (function () {
        function GlobalConstants() {
        }
        return GlobalConstants;
    }());
    //public static apiURL: string = "http://localhost/matomo/index.php/";
    GlobalConstants.apiURL = "https://demo.matomo.cloud/";

    var DashboardService = /** @class */ (function () {
        function DashboardService(http, router, serializer) {
            this.http = http;
            this.router = router;
            this.serializer = serializer;
            this.refreshPromise = null;
            // those sites are going to be displayed
            this.model = {
                sites: [],
                isLoading: false,
                pageSize: 25,
                currentPage: 0,
                totalVisits: '?',
                totalPageviews: '?',
                totalActions: '?',
                totalRevenue: '?',
                searchTerm: '',
                lastVisits: '?',
                lastVisitsDate: '?',
                numberOfSites: 0,
                loadingMessage: 'Loading data...',
                updateWebsitesList: this.updateWebsitesList,
                getNumberOfFilteredSites: this.getNumberOfFilteredSites,
                getNumberOfPages: this.getNumberOfPages,
                getPaginationLowerBound: this.getPaginationLowerBound,
                getPaginationUpperBound: this.getPaginationUpperBound,
                previousPage: this.previousPage,
                nextPage: this.nextPage,
                searchSite: this.searchSite,
                sortBy: this.sortBy,
                reverse: true,
                sortColumn: 'nb_visits',
                fetchAllSites: this.fetchAllSites,
                refreshInterval: 0
            };
        }
        DashboardService.prototype.cancelRefereshInterval = function () {
            if (this.refreshPromise) {
                clearTimeout(this.refreshPromise);
                this.refreshPromise = null;
            }
            ;
        };
        DashboardService.prototype.onError = function () {
            this.model.errorLoadingSites = true;
            this.model.sites = [];
        };
        DashboardService.prototype.getCurrentPagingOffset = function () {
            return Math.ceil(this.model.currentPage * this.model.pageSize);
        };
        DashboardService.prototype.fetchAllSites = function () {
            var _this = this;
            if (this.model.isLoading) {
                //piwikApi.abort();
                this.cancelRefereshInterval();
            }
            this.model.isLoading = true;
            this.model.errorLoadingSites = false;
            var params = {
                module: 'MultiSites',
                action: 'getAllWithGroups',
                hideMetricsDoc: '1',
                filter_sort_order: 'asc',
                filter_limit: this.model.pageSize,
                filter_offset: this.getCurrentPagingOffset(),
                showColumns: 'label,nb_visits,nb_pageviews,visits_evolution,pageviews_evolution,revenue_evolution,nb_actions,revenue',
                idSite: 1,
                period: 'day',
                date: 'yesterday',
            };
            if (this.model.searchTerm) {
                params.pattern = this.model.searchTerm;
            }
            if (this.model.sortColumn) {
                params.filter_sort_column = this.model.sortColumn;
            }
            if (this.model.reverse) {
                params.filter_sort_order = 'desc';
            }
            params.module = params.module || 'API';
            if (!params.format) {
                params.format = 'JSON';
            }
            var tree = this.router.createUrlTree([], { queryParams: params });
            this.http.post(GlobalConstants.apiURL + this.serializer.serialize(tree).replace('/?', '?'), null).subscribe(function (response) {
                _this.updateWebsitesList(response);
            }, this.onError, function () {
                _this.model.isLoading = false;
                if (_this.model.refreshInterval && _this.model.refreshInterval > 0) {
                    _this.cancelRefereshInterval();
                    _this.refreshPromise = setTimeout(function () {
                        _this.refreshPromise = null;
                        _this.fetchAllSites();
                    }, _this.model.refreshInterval * 1000);
                }
            });
        };
        DashboardService.prototype.updateWebsitesList = function (report) {
            if (!report) {
                this.onError();
                return;
            }
            var allSites = report.sites;
            allSites.forEach(function (site) {
                site.visits_evolution = parseInt(site.visits_evolution, 10);
                site.pageviews_evolution = parseInt(site.pageviews_evolution, 10);
                site.revenue_evolution = parseInt(site.revenue_evolution, 10);
            });
            this.model.totalVisits = report.totals.nb_visits;
            this.model.totalPageviews = report.totals.nb_pageviews;
            this.model.totalActions = report.totals.nb_actions;
            this.model.totalRevenue = report.totals.revenue;
            this.model.lastVisits = report.totals.nb_visits_lastdate;
            this.model.sites = allSites;
            this.model.numberOfSites = report.numSites;
            this.model.lastVisitsDate = report.lastDate;
        };
        DashboardService.prototype.getNumberOfFilteredSites = function () {
            if (this.model) {
                return this.model.numberOfSites;
            }
        };
        DashboardService.prototype.getNumberOfPages = function () {
            if (this.model) {
                return Math.ceil(this.getNumberOfFilteredSites() / this.model.pageSize - 1);
            }
        };
        DashboardService.prototype.getPaginationLowerBound = function () {
            return this.getCurrentPagingOffset() + 1;
        };
        DashboardService.prototype.getPaginationUpperBound = function () {
            if (this.model) {
                var end = this.getCurrentPagingOffset() + parseInt(this.model.pageSize, 10);
                var max = this.getNumberOfFilteredSites();
                if (end > max) {
                    end = max;
                }
                return parseInt(end.toString(), 10);
            }
        };
        DashboardService.prototype.sortBy = function (metric) {
            if (this.model) {
                if (this.model.sortColumn == metric) {
                    this.model.reverse = !this.model.reverse;
                }
                this.model.sortColumn = metric;
                this.fetchAllSites();
            }
        };
        ;
        DashboardService.prototype.previousPage = function () {
            if (this.model) {
                this.model.currentPage = this.model.currentPage - 1;
                this.fetchAllSites();
            }
        };
        DashboardService.prototype.nextPage = function () {
            if (this.model) {
                this.model.currentPage = this.model.currentPage + 1;
                this.fetchAllSites();
            }
        };
        DashboardService.prototype.searchSite = function (term) {
            this.model.searchTerm = term;
            this.model.currentPage = 0;
            this.fetchAllSites();
        };
        return DashboardService;
    }());
    DashboardService.ɵfac = function DashboardService_Factory(t) { return new (t || DashboardService)(i0.ɵɵinject(i1.HttpClient), i0.ɵɵinject(i2.Router), i0.ɵɵinject(i2.UrlSerializer)); };
    DashboardService.ɵprov = i0.ɵɵdefineInjectable({ token: DashboardService, factory: DashboardService.ɵfac, providedIn: 'root' });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(DashboardService, [{
                type: i0.Injectable,
                args: [{
                        providedIn: 'root'
                    }]
            }], function () { return [{ type: i1.HttpClient }, { type: i2.Router }, { type: i2.UrlSerializer }]; }, null);
    })();

    function SiteComponent_td_0_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "td", 5);
            i0.ɵɵelementStart(1, "a", 6);
            i0.ɵɵtext(2);
            i0.ɵɵelementEnd();
            i0.ɵɵelementStart(3, "span");
            i0.ɵɵelementStart(4, "a", 7);
            i0.ɵɵelement(5, "span", 8);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r0 = i0.ɵɵnextContext();
            i0.ɵɵadvance(1);
            i0.ɵɵpropertyInterpolate3("href", "index.php?module=CoreHome&action=index&date=", ctx_r0.date, "&period=", ctx_r0.period, "&idSite=", ctx_r0.website.idsite, "", i0.ɵɵsanitizeUrl);
            i0.ɵɵadvance(1);
            i0.ɵɵtextInterpolate(ctx_r0.website.label);
            i0.ɵɵadvance(2);
            i0.ɵɵproperty("href", ctx_r0.website.main_url, i0.ɵɵsanitizeUrl);
            i0.ɵɵattribute("title", ctx_r0.website.main_url);
        }
    }
    function SiteComponent_td_1_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "td", 5);
            i0.ɵɵelementStart(1, "span", 2);
            i0.ɵɵtext(2);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r1 = i0.ɵɵnextContext();
            i0.ɵɵadvance(2);
            i0.ɵɵtextInterpolate(ctx_r1.website.label);
        }
    }
    function SiteComponent_td_11_div_1_span_1_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "span");
            i0.ɵɵelement(1, "img", 13);
            i0.ɵɵelementStart(2, "span", 14);
            i0.ɵɵtext(3);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r5 = i0.ɵɵnextContext(3);
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate1("", ctx_r5.website[ctx_r5.evolutionMetric], "%");
        }
    }
    function SiteComponent_td_11_div_1_span_2_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "span");
            i0.ɵɵelement(1, "img", 15);
            i0.ɵɵelementStart(2, "span");
            i0.ɵɵtext(3);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r6 = i0.ɵɵnextContext(3);
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate1("", ctx_r6.website[ctx_r6.evolutionMetric], "%");
        }
    }
    function SiteComponent_td_11_div_1_span_3_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "span");
            i0.ɵɵelement(1, "img", 16);
            i0.ɵɵelementStart(2, "span", 17);
            i0.ɵɵtext(3);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r7 = i0.ɵɵnextContext(3);
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate1("", ctx_r7.website[ctx_r7.evolutionMetric], "%");
        }
    }
    function SiteComponent_td_11_div_1_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "div", 11);
            i0.ɵɵtemplate(1, SiteComponent_td_11_div_1_span_1_Template, 4, 1, "span", 12);
            i0.ɵɵtemplate(2, SiteComponent_td_11_div_1_span_2_Template, 4, 1, "span", 12);
            i0.ɵɵtemplate(3, SiteComponent_td_11_div_1_span_3_Template, 4, 1, "span", 12);
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r4 = i0.ɵɵnextContext(2);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r4.website[ctx_r4.evolutionMetric] > 0);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r4.website[ctx_r4.evolutionMetric] == 0);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r4.website[ctx_r4.evolutionMetric] < 0);
        }
    }
    function SiteComponent_td_11_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "td", 9);
            i0.ɵɵtemplate(1, SiteComponent_td_11_div_1_Template, 4, 3, "div", 10);
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r2 = i0.ɵɵnextContext();
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", !ctx_r2.website.isGroup);
        }
    }
    function SiteComponent_td_12_div_1_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "div", 20);
            i0.ɵɵelementStart(1, "a", 21);
            i0.ɵɵelement(2, "img", 22);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r8 = i0.ɵɵnextContext(2);
            i0.ɵɵadvance(1);
            i0.ɵɵpropertyInterpolate3("href", "index.php?module=CoreHome&action=index&date=", ctx_r8.date, "&period=", ctx_r8.period, "&idSite=", ctx_r8.website.idsite, "", i0.ɵɵsanitizeUrl);
            i0.ɵɵpropertyInterpolate1("title", "Go to ", ctx_r8.website.label, "");
            i0.ɵɵadvance(1);
            i0.ɵɵpropertyInterpolate("src", ctx_r8.sparklineImage(ctx_r8.website), i0.ɵɵsanitizeUrl);
        }
    }
    function SiteComponent_td_12_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "td", 18);
            i0.ɵɵtemplate(1, SiteComponent_td_12_div_1_Template, 3, 5, "div", 19);
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r3 = i0.ɵɵnextContext();
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", !ctx_r3.website.isGroup);
        }
    }
    var SiteComponent = /** @class */ (function () {
        function SiteComponent() {
            this.period = 'day';
            this.date = 'yesterday';
        }
        SiteComponent.prototype.ngOnInit = function () {
        };
        SiteComponent.prototype.sparklineImage = function () {
            var append = '';
            var token_auth = '';
            if (token_auth.length) {
                append = '&token_auth=' + token_auth;
            }
            var metric = this.metric;
            switch (this.evolutionMetric) {
                case 'visits_evolution':
                    metric = 'nb_visits';
                    break;
                case 'pageviews_evolution':
                    metric = 'nb_pageviews';
                    break;
                case 'revenue_evolution':
                    metric = 'revenue';
                    break;
            }
            // Need to add below code while returning sparkline chart one we create CoreHome development with sparkline chartJs
            //&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors()));
            return GlobalConstants.apiURL + '?module=MultiSites&action=getEvolutionGraph&period=' + this.period + '&date=' + this.dateSparkline + '&evolutionBy=' + metric + '&columns=' + metric + '&idSite=' + this.website.idsite + '&idsite=' + this.website.idsite + '&viewDataTable=sparkline' + append;
        };
        return SiteComponent;
    }());
    SiteComponent.ɵfac = function SiteComponent_Factory(t) { return new (t || SiteComponent)(); };
    SiteComponent.ɵcmp = i0.ɵɵdefineComponent({ type: SiteComponent, selectors: [["piwik-site"]], inputs: { website: "website", evolutionMetric: "evolutionMetric", showSparklines: "showSparklines", metric: "metric", displayRevenueColumn: "displayRevenueColumn", dateSparkline: "dateSparkline" }, decls: 13, vars: 7, consts: [["class", "multisites-label label", 4, "ngIf"], [1, "multisites-column", "text-aligh-right"], [1, "value"], ["class", "multisites-evolution text-align-center", 4, "ngIf"], ["class", "td-sparkline", 4, "ngIf"], [1, "multisites-label", "label"], ["title", "View reports", 1, "value", "truncated-text-line", 3, "href"], ["rel", "noreferrer noopener", "target", "_blank", 3, "href"], [1, "icon", "icon-outlink"], [1, "multisites-evolution", "text-align-center"], ["class", "visits value", 4, "ngIf"], [1, "visits", "value"], [4, "ngIf"], ["src", "assets/arrow_up.png", "alt", ""], [2, "color", "green"], ["src", "assets/stop.png", "alt", ""], ["src", "assets/arrow_down.png", "alt", ""], [2, "color", "red"], [1, "td-sparkline"], ["class", "sparkline", 4, "ngIf"], [1, "sparkline"], ["rel", "noreferrer noopener", "target", "_blank", 3, "href", "title"], ["alt", "", "width", "100", "height", "25", 3, "src"]], template: function SiteComponent_Template(rf, ctx) {
            if (rf & 1) {
                i0.ɵɵtemplate(0, SiteComponent_td_0_Template, 6, 6, "td", 0);
                i0.ɵɵtemplate(1, SiteComponent_td_1_Template, 3, 1, "td", 0);
                i0.ɵɵelementStart(2, "td", 1);
                i0.ɵɵelementStart(3, "span", 2);
                i0.ɵɵtext(4);
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(5, "td", 1);
                i0.ɵɵelementStart(6, "span", 2);
                i0.ɵɵtext(7);
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(8, "td", 1);
                i0.ɵɵelementStart(9, "span", 2);
                i0.ɵɵtext(10);
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵtemplate(11, SiteComponent_td_11_Template, 2, 1, "td", 3);
                i0.ɵɵtemplate(12, SiteComponent_td_12_Template, 2, 1, "td", 4);
            }
            if (rf & 2) {
                i0.ɵɵproperty("ngIf", !ctx.website.isGroup);
                i0.ɵɵadvance(1);
                i0.ɵɵproperty("ngIf", ctx.website.isGroup);
                i0.ɵɵadvance(3);
                i0.ɵɵtextInterpolate(ctx.website.nb_visits);
                i0.ɵɵadvance(3);
                i0.ɵɵtextInterpolate(ctx.website.nb_pageviews);
                i0.ɵɵadvance(3);
                i0.ɵɵtextInterpolate(ctx.website.revenue);
                i0.ɵɵadvance(1);
                i0.ɵɵproperty("ngIf", ctx.period != "range");
                i0.ɵɵadvance(1);
                i0.ɵɵproperty("ngIf", ctx.showSparklines);
            }
        }, directives: [i2$1.NgIf], styles: [".text-aligh-right[_ngcontent-%COMP%]{text-align:right}.text-align-center[_ngcontent-%COMP%]{text-align:center}.td-sparkline[_ngcontent-%COMP%]{width:180px}"] });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(SiteComponent, [{
                type: i0.Component,
                args: [{
                        selector: 'piwik-site',
                        templateUrl: './site.component.html',
                        styleUrls: ['./site.component.less']
                    }]
            }], function () { return []; }, { website: [{
                    type: i0.Input
                }], evolutionMetric: [{
                    type: i0.Input
                }], showSparklines: [{
                    type: i0.Input
                }], metric: [{
                    type: i0.Input
                }], displayRevenueColumn: [{
                    type: i0.Input
                }], dateSparkline: [{
                    type: i0.Input
                }] });
    })();

    function DashboardComponent_option_31_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "option", 22);
            i0.ɵɵtext(1, "Revenue");
            i0.ɵɵelementEnd();
        }
    }
    function DashboardComponent_tbody_32_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "tbody", 23);
            i0.ɵɵelementStart(1, "tr");
            i0.ɵɵelementStart(2, "td", 24);
            i0.ɵɵelement(3, "piwik-activity-indicator", 25);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r1 = i0.ɵɵnextContext();
            i0.ɵɵadvance(3);
            i0.ɵɵproperty("loadingMessage", ctx_r1.model.model.loadingMessage)("loading", ctx_r1.model.model.isLoading);
        }
    }
    function DashboardComponent_tbody_33_tr_1_span_12_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "span");
            i0.ɵɵtext(1, " \u2013 ");
            i0.ɵɵelementEnd();
        }
    }
    function DashboardComponent_tbody_33_tr_1_a_13_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "a", 33);
            i0.ɵɵtext(1);
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r7 = i0.ɵɵnextContext(3);
            i0.ɵɵadvance(1);
            i0.ɵɵtextInterpolate(ctx_r7.professionalHelp);
        }
    }
    function DashboardComponent_tbody_33_tr_1_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "tr");
            i0.ɵɵelementStart(1, "td", 28);
            i0.ɵɵelementStart(2, "div", 29);
            i0.ɵɵtext(3);
            i0.ɵɵelement(4, "br");
            i0.ɵɵelement(5, "br");
            i0.ɵɵtext(6);
            i0.ɵɵelementStart(7, "a", 30);
            i0.ɵɵtext(8);
            i0.ɵɵelementEnd();
            i0.ɵɵtext(9, " \u2013 ");
            i0.ɵɵelementStart(10, "a", 31);
            i0.ɵɵtext(11);
            i0.ɵɵelementEnd();
            i0.ɵɵtemplate(12, DashboardComponent_tbody_33_tr_1_span_12_Template, 2, 0, "span", 26);
            i0.ɵɵtemplate(13, DashboardComponent_tbody_33_tr_1_a_13_Template, 2, 1, "a", 32);
            i0.ɵɵtext(14, ". ");
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r4 = i0.ɵɵnextContext(2);
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate1(" ", ctx_r4.errorMessage, " ");
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate1(" ", ctx_r4.needMoreHelp, " ");
            i0.ɵɵadvance(2);
            i0.ɵɵtextInterpolate(ctx_r4.faq);
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate(ctx_r4.communityHelp);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r4.areAdsForProfessionalServicesEnabled);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r4.areAdsForProfessionalServicesEnabled);
        }
    }
    function DashboardComponent_tbody_33_piwik_site_2_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelement(0, "piwik-site", 34);
        }
        if (rf & 2) {
            var website_r8 = ctx.$implicit;
            var ctx_r5 = i0.ɵɵnextContext(2);
            i0.ɵɵproperty("website", website_r8)("showSparklines", ctx_r5.showSparklines)("evolutionMetric", ctx_r5.evolutionSelector)("metric", ctx_r5.model.sortColumn)("displayRevenueColumn", ctx_r5.displayRevenueColumn)("dateSparkline", ctx_r5.dateSparkline);
        }
    }
    function DashboardComponent_tbody_33_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "tbody", 23);
            i0.ɵɵtemplate(1, DashboardComponent_tbody_33_tr_1_Template, 15, 6, "tr", 26);
            i0.ɵɵtemplate(2, DashboardComponent_tbody_33_piwik_site_2_Template, 1, 6, "piwik-site", 27);
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r2 = i0.ɵɵnextContext();
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r2.model.model.errorLoadingSites);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngForOf", ctx_r2.model.model.sites);
        }
    }
    function DashboardComponent_td_36_a_3_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "a", 44);
            i0.ɵɵelement(1, "span", 45);
            i0.ɵɵtext(2, " Add a new website ");
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r9 = i0.ɵɵnextContext(2);
            i0.ɵɵpropertyInterpolate4("href", "", ctx_r9.url, "?module=SitesManager&action=index&showaddsite=1&period=", ctx_r9.period, "&date=", ctx_r9.date, "&idSite=", ctx_r9.idSite, "", i0.ɵɵsanitizeUrl);
        }
    }
    function DashboardComponent_td_36_span_5_Template(rf, ctx) {
        if (rf & 1) {
            var _r13_1 = i0.ɵɵgetCurrentView();
            i0.ɵɵelementStart(0, "span", 46);
            i0.ɵɵlistener("click", function DashboardComponent_td_36_span_5_Template_span_click_0_listener() { i0.ɵɵrestoreView(_r13_1); var ctx_r12 = i0.ɵɵnextContext(2); return ctx_r12.model.previousPage(); });
            i0.ɵɵelementStart(1, "span", 47);
            i0.ɵɵtext(2, "\u00AB Previous");
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
    }
    function DashboardComponent_td_36_span_9_Template(rf, ctx) {
        if (rf & 1) {
            var _r15_1 = i0.ɵɵgetCurrentView();
            i0.ɵɵelementStart(0, "span", 48);
            i0.ɵɵlistener("click", function DashboardComponent_td_36_span_9_Template_span_click_0_listener() { i0.ɵɵrestoreView(_r15_1); var ctx_r14 = i0.ɵɵnextContext(2); return ctx_r14.model.nextPage(); });
            i0.ɵɵelementStart(1, "span", 49);
            i0.ɵɵtext(2, "Next \u00BB");
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
    }
    function DashboardComponent_td_36_Template(rf, ctx) {
        if (rf & 1) {
            i0.ɵɵelementStart(0, "td", 35);
            i0.ɵɵelementStart(1, "div", 18);
            i0.ɵɵelementStart(2, "div", 36);
            i0.ɵɵtemplate(3, DashboardComponent_td_36_a_3_Template, 3, 4, "a", 37);
            i0.ɵɵelementEnd();
            i0.ɵɵelementStart(4, "div", 38);
            i0.ɵɵtemplate(5, DashboardComponent_td_36_span_5_Template, 3, 0, "span", 39);
            i0.ɵɵelementStart(6, "span", 40);
            i0.ɵɵelementStart(7, "span", 41);
            i0.ɵɵtext(8);
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
            i0.ɵɵtemplate(9, DashboardComponent_td_36_span_9_Template, 3, 0, "span", 42);
            i0.ɵɵelementEnd();
            i0.ɵɵelementStart(10, "div", 43);
            i0.ɵɵtext(11, "\u00A0");
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
            i0.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r3 = i0.ɵɵnextContext();
            i0.ɵɵadvance(3);
            i0.ɵɵproperty("ngIf", ctx_r3.hasSuperUserAccess);
            i0.ɵɵadvance(2);
            i0.ɵɵproperty("ngIf", ctx_r3.model.currentPage > 0);
            i0.ɵɵadvance(3);
            i0.ɵɵtextInterpolate3(" ", ctx_r3.model.getPaginationLowerBound(), "\u2013", ctx_r3.model.getPaginationUpperBound(), " of ", ctx_r3.model.getNumberOfFilteredSites(), " ");
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx_r3.model.currentPage <= ctx_r3.model.getNumberOfPages());
        }
    }
    var _c0 = function (a0, a1) { return { multisites_asc: a0, multisites_desc: a1 }; };
    var DashboardComponent = /** @class */ (function () {
        function DashboardComponent(dbService) {
            this.dbService = dbService;
            this.evolutionSelector = 'visits_evolution';
            this.hasSuperUserAccess = true;
            this.date = 'yesterday';
            this.url = GlobalConstants.apiURL;
            this.period = 'day';
            this.areAdsForProfessionalServicesEnabled = true;
            this.displayRevenueColumn = true;
            this.showSparklines = true;
            this.dateSparkline = '2020-12-27,2021-02-25';
            // This are temporary labels which will be made generic in the future
            // Reason : As translate filter is not developed yet
            this.faq = "FAQ";
            this.communityHelp = "Community Help";
            this.needMoreHelp = "Need more help?";
            this.errorMessage = "Oops… there was a problem during the request. Maybe the server had a temporary issue, or maybe you requested a report with too much data. Please try again. If this error occurs repeatedly please %1$scontact your Matomo administrator%2$s for assistance.";
            this.objJSON = JSON;
        }
        DashboardComponent.prototype.ngOnInit = function () {
            this.model = this.dbService;
            this.refresh(this.model.model.refreshInterval);
            console.log(this.model.model);
        };
        DashboardComponent.prototype.refresh = function (interval) {
            this.model.refreshInterval = interval;
            this.model.fetchAllSites();
        };
        ;
        return DashboardComponent;
    }());
    DashboardComponent.ɵfac = function DashboardComponent_Factory(t) { return new (t || DashboardComponent)(i0.ɵɵdirectiveInject(DashboardService)); };
    DashboardComponent.ɵcmp = i0.ɵɵdefineComponent({ type: DashboardComponent, selectors: [["piwik-dashboard"]], decls: 43, vars: 10, consts: [["id", "mt", "cellspacing", "20", 1, "dataTable", "card-table"], ["id", "names", 1, "label", "cursor-pointer", 3, "click"], [1, "heading"], [1, "arrow", 3, "ngClass"], ["id", "visits", 1, "multisites-column", "text-aligh-right", "cursor-pointer", 3, "click"], [1, "arrow"], [1, "multisites-column", "text-aligh-right", "cursor-pointer", 3, "click"], ["id", "revenue", 1, "multisites-column", "text-aligh-right", "cursor-pointer", 3, "click"], ["id", "evolution", 1, "cursor-pointer"], [1, "evolution", 3, "click"], ["id", "evolution_selector", 1, "selector", "browser-default", 3, "ngModel", "ngModelChange", "change"], ["value", "visits_evolution"], ["value", "pageviews_evolution"], ["value", "revenue_evolution", 4, "ngIf"], ["id", "tb", 4, "ngIf"], ["colspan", "8", "class", "paging", 4, "ngIf"], ["row_id", "last"], ["colspan", "8", 1, "site_search"], [1, "row"], [1, "input-field", "col", "s12"], ["type", "text", "placeholder", "Site Search"], ["title", "search", 1, "icon-search", "search_ico"], ["value", "revenue_evolution"], ["id", "tb"], ["colspan", "7", 1, "allWebsitesLoading"], [3, "loadingMessage", "loading"], [4, "ngIf"], ["class", "datatable-tr", 3, "website", "showSparklines", "evolutionMetric", "metric", "displayRevenueColumn", "dateSparkline", 4, "ngFor", "ngForOf"], ["colspan", "7"], [1, "notification", "system", "notification-error"], ["rel", "noreferrer noopener", "target", "_blank", "href", "https://matomo.org/faq/troubleshooting/faq_19489/"], ["rel", "noreferrer noopener", "target", "_blank", "href", "https://forum.matomo.org/"], ["rel", "noreferrer noopener", "target", "_blank", "href", "https://matomo.org/support-plans/?pk_campaign=Help&pk_medium=AjaxError&pk_content=MultiSites&pk_source=Matomo_App", 4, "ngIf"], ["rel", "noreferrer noopener", "target", "_blank", "href", "https://matomo.org/support-plans/?pk_campaign=Help&pk_medium=AjaxError&pk_content=MultiSites&pk_source=Matomo_App"], [1, "datatable-tr", 3, "website", "showSparklines", "evolutionMetric", "metric", "displayRevenueColumn", "dateSparkline"], ["colspan", "8", 1, "paging"], [1, "col", "s3", "add_new_site"], [3, "href", 4, "ngIf"], [1, "col", "s6"], ["id", "prev", "class", "previous dataTablePrevious", 3, "click", 4, "ngIf"], [1, "dataTablePages"], ["id", "counter"], ["id", "next", "class", "next dataTableNext", 3, "click", 4, "ngIf"], [1, "col", "s3"], [3, "href"], [1, "icon-add"], ["id", "prev", 1, "previous", "dataTablePrevious", 3, "click"], [2, "cursor", "pointer"], ["id", "next", 1, "next", "dataTableNext", 3, "click"], [1, "pointer", 2, "cursor", "pointer"]], template: function DashboardComponent_Template(rf, ctx) {
            if (rf & 1) {
                i0.ɵɵelementStart(0, "div");
                i0.ɵɵelementStart(1, "h2");
                i0.ɵɵtext(2, " All Websites dashboard ");
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(3, "table", 0);
                i0.ɵɵelementStart(4, "thead");
                i0.ɵɵelementStart(5, "tr");
                i0.ɵɵelementStart(6, "th", 1);
                i0.ɵɵlistener("click", function DashboardComponent_Template_th_click_6_listener() { return ctx.model.sortBy("label"); });
                i0.ɵɵelementStart(7, "span", 2);
                i0.ɵɵtext(8, "WEBSITE");
                i0.ɵɵelementEnd();
                i0.ɵɵelement(9, "span", 3);
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(10, "th", 4);
                i0.ɵɵlistener("click", function DashboardComponent_Template_th_click_10_listener() { return ctx.model.sortBy("nb_visits"); });
                i0.ɵɵelement(11, "span", 5);
                i0.ɵɵelementStart(12, "span", 2);
                i0.ɵɵtext(13, "VISITS");
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(14, "th", 6);
                i0.ɵɵlistener("click", function DashboardComponent_Template_th_click_14_listener() { return ctx.model.sortBy("nb_pageviews"); });
                i0.ɵɵelement(15, "span", 5);
                i0.ɵɵelementStart(16, "span", 2);
                i0.ɵɵtext(17, "PAGEVIEWS");
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(18, "th", 7);
                i0.ɵɵlistener("click", function DashboardComponent_Template_th_click_18_listener() { return ctx.model.sortBy("revenue"); });
                i0.ɵɵelement(19, "span", 5);
                i0.ɵɵelementStart(20, "span", 2);
                i0.ɵɵtext(21, "REVENUE");
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(22, "th", 8);
                i0.ɵɵelement(23, "span", 5);
                i0.ɵɵelementStart(24, "span", 9);
                i0.ɵɵlistener("click", function DashboardComponent_Template_span_click_24_listener() { return ctx.model.sortBy(ctx.evolutionSelector); });
                i0.ɵɵtext(25, " EVOLUTION ");
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(26, "select", 10);
                i0.ɵɵlistener("ngModelChange", function DashboardComponent_Template_select_ngModelChange_26_listener($event) { return ctx.evolutionSelector = $event; })("change", function DashboardComponent_Template_select_change_26_listener() { return ctx.model.sortBy(ctx.evolutionSelector); });
                i0.ɵɵelementStart(27, "option", 11);
                i0.ɵɵtext(28, "Visits");
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(29, "option", 12);
                i0.ɵɵtext(30, "Pageviews");
                i0.ɵɵelementEnd();
                i0.ɵɵtemplate(31, DashboardComponent_option_31_Template, 2, 0, "option", 13);
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵtemplate(32, DashboardComponent_tbody_32_Template, 4, 2, "tbody", 14);
                i0.ɵɵtemplate(33, DashboardComponent_tbody_33_Template, 3, 2, "tbody", 14);
                i0.ɵɵelementStart(34, "tfoot");
                i0.ɵɵelementStart(35, "tr");
                i0.ɵɵtemplate(36, DashboardComponent_td_36_Template, 12, 6, "td", 15);
                i0.ɵɵelementEnd();
                i0.ɵɵelementStart(37, "tr", 16);
                i0.ɵɵelementStart(38, "td", 17);
                i0.ɵɵelementStart(39, "div", 18);
                i0.ɵɵelementStart(40, "div", 19);
                i0.ɵɵelement(41, "input", 20);
                i0.ɵɵelement(42, "span", 21);
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
                i0.ɵɵelementEnd();
            }
            if (rf & 2) {
                i0.ɵɵadvance(9);
                i0.ɵɵproperty("ngClass", i0.ɵɵpureFunction2(7, _c0, !ctx.model.reverse && "label" == ctx.model.sortColumn, ctx.model.reverse && "label" == ctx.model.sortColumn));
                i0.ɵɵadvance(13);
                i0.ɵɵattribute("colspan", ctx.showSparklines ? 2 : 1);
                i0.ɵɵadvance(4);
                i0.ɵɵproperty("ngModel", ctx.evolutionSelector);
                i0.ɵɵadvance(5);
                i0.ɵɵproperty("ngIf", ctx.displayRevenueColumn);
                i0.ɵɵadvance(1);
                i0.ɵɵproperty("ngIf", ctx.model.model.isLoading);
                i0.ɵɵadvance(1);
                i0.ɵɵproperty("ngIf", !ctx.model.model.isLoading);
                i0.ɵɵadvance(3);
                i0.ɵɵproperty("ngIf", ctx.model.getNumberOfPages() >= 0);
            }
        }, directives: [i2$1.NgClass, i3.SelectControlValueAccessor, i3.NgControlStatus, i3.NgModel, i3.NgSelectOption, i3.ɵangular_packages_forms_forms_x, i2$1.NgIf, i4.ActivityIndicatorComponent, i2$1.NgForOf, SiteComponent], styles: [".smallTitle[_ngcontent-%COMP%]{font-size:15px}.widget[_ngcontent-%COMP%]   #multisites[_ngcontent-%COMP%]{padding:15px}#mt[_ngcontent-%COMP%]   table.dataTable[_ngcontent-%COMP%]   td.label[_ngcontent-%COMP%]   img[_ngcontent-%COMP%]{margin-top:-8px}#multisites[_ngcontent-%COMP%] > .col[_ngcontent-%COMP%]{padding-left:0;padding-right:0}#multisites[_ngcontent-%COMP%]   .notification-error[_ngcontent-%COMP%]{margin-top:15px}#multisites[_ngcontent-%COMP%]   .notification-error[_ngcontent-%COMP%]   a[_ngcontent-%COMP%]{text-decoration:underline!important}#multisites[_ngcontent-%COMP%]   .add_new_site[_ngcontent-%COMP%]{border:0!important;font-size:13px;text-align:left;padding-left:27px}#multisites[_ngcontent-%COMP%]   .add_new_site[_ngcontent-%COMP%]   a[_ngcontent-%COMP%]{color:#0d0d0d}#multisites[_ngcontent-%COMP%]   .add_new_site[_ngcontent-%COMP%]   a[_ngcontent-%COMP%]:hover{text-decoration:underline!important}#multisites[_ngcontent-%COMP%]   .clean[_ngcontent-%COMP%]{border:0!important;text-align:right;padding-right:10px;padding-top:19px;padding-bottom:5px}@media print{#multisites[_ngcontent-%COMP%]   .add_new_site[_ngcontent-%COMP%]{display:none}#multisites[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .col.s6[_ngcontent-%COMP%]{width:100%}#multisites[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .col.s3[_ngcontent-%COMP%]{display:none}}#multisites[_ngcontent-%COMP%]   .site_search[_ngcontent-%COMP%]{padding:0;text-align:center;border:0!important}@media print{#multisites[_ngcontent-%COMP%]   .site_search[_ngcontent-%COMP%]{display:none}}#multisites[_ngcontent-%COMP%]   .multisites-column[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   .multisites-evolution[_ngcontent-%COMP%]{text-align:right}#multisites[_ngcontent-%COMP%]   .multisites-evolution[_ngcontent-%COMP%]{width:170px}#multisites[_ngcontent-%COMP%]   .sparkline[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   td[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]{text-align:center;vertical-align:middle}#multisites[_ngcontent-%COMP%]   td.empty-row[_ngcontent-%COMP%]{border-bottom:none!important}#multisites[_ngcontent-%COMP%]   .paging[_ngcontent-%COMP%]{padding:5px;border-bottom:0!important}#multisites[_ngcontent-%COMP%]   .paging[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]{margin-top:16px}#multisites[_ngcontent-%COMP%]   .paging[_ngcontent-%COMP%]   .next[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   .paging[_ngcontent-%COMP%]   .previous[_ngcontent-%COMP%]{visibility:visible}#multisites[_ngcontent-%COMP%]   th[_ngcontent-%COMP%]:first-child{text-align:left;padding-left:32px}#multisites[_ngcontent-%COMP%]   th[_ngcontent-%COMP%]{cursor:pointer;text-align:right;padding-right:0!important}#multisites[_ngcontent-%COMP%]   th#evolution[_ngcontent-%COMP%]{text-align:center}#multisites[_ngcontent-%COMP%]   th.columnSorted[_ngcontent-%COMP%]{font-weight:400!important}#multisites[_ngcontent-%COMP%]   .site_search[_ngcontent-%COMP%]   input[_ngcontent-%COMP%]{margin-right:0;margin-left:25px;padding-right:25px;width:250px;height:3rem;padding-left:5px}#multisites[_ngcontent-%COMP%]   .site_search[_ngcontent-%COMP%]   label[_ngcontent-%COMP%]{position:static}#multisites[_ngcontent-%COMP%]   .site_search[_ngcontent-%COMP%]   .input-field[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   .site_search[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]{margin-bottom:0}#multisites[_ngcontent-%COMP%]   .search_ico[_ngcontent-%COMP%]{position:relative;left:-30px;top:1px;cursor:pointer;font-size:16px}#multisites[_ngcontent-%COMP%]   .reset[_ngcontent-%COMP%]{position:relative;left:-25px;cursor:pointer;margin-right:0}#multisites[_ngcontent-%COMP%]   tr.group[_ngcontent-%COMP%]{font-weight:700;height:30px}#multisites[_ngcontent-%COMP%]   tr.groupedWebsite[_ngcontent-%COMP%]   .label[_ngcontent-%COMP%]{padding-left:50px}#multisites[_ngcontent-%COMP%]   td.multisites-label[_ngcontent-%COMP%]{text-align:left;width:250px;max-width:250px;padding-left:32px}#multisites[_ngcontent-%COMP%]   td.multisites-label[_ngcontent-%COMP%]   a[_ngcontent-%COMP%]{width:auto;float:left;padding-right:.5em}#multisites[_ngcontent-%COMP%]   td.multisites-label[_ngcontent-%COMP%]   a[_ngcontent-%COMP%]:hover{text-decoration:underline}#multisites[_ngcontent-%COMP%]   td.multisites-column[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   th.multisites-column[_ngcontent-%COMP%]{width:70px;white-space:nowrap}#multisites[_ngcontent-%COMP%]   td.multisites-column-evolution[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   th.multisites-column-evolution[_ngcontent-%COMP%]{width:70px}#multisites[_ngcontent-%COMP%]   th#evolution[_ngcontent-%COMP%]{width:350px}#multisites[_ngcontent-%COMP%]   th#visits[_ngcontent-%COMP%]{width:100px}#multisites[_ngcontent-%COMP%]   th#pageviews[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   th#revenue[_ngcontent-%COMP%]{width:110px}#multisites[_ngcontent-%COMP%]   .evolution[_ngcontent-%COMP%]{cursor:pointer}#multisites[_ngcontent-%COMP%]   .allWebsitesLoading[_ngcontent-%COMP%]{padding:20px}#multisites[_ngcontent-%COMP%]   .heading[_ngcontent-%COMP%]{display:inline;margin-top:4px}#multisites[_ngcontent-%COMP%]   #evolution_selector[_ngcontent-%COMP%]{height:28px;margin:-9px 0 0 5px;width:80px;display:inline-block}#multisites[_ngcontent-%COMP%]   .label[_ngcontent-%COMP%]   .arrow[_ngcontent-%COMP%]{margin-left:2px}#multisites[_ngcontent-%COMP%]   .multisites_asc[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   .multisites_desc[_ngcontent-%COMP%]{float:none;display:inline-block;vertical-align:top;margin:-1px 0 0 6px}#multisites[_ngcontent-%COMP%]   #evolution[_ngcontent-%COMP%]   .multisites_asc[_ngcontent-%COMP%], #multisites[_ngcontent-%COMP%]   #evolution[_ngcontent-%COMP%]   .multisites_desc[_ngcontent-%COMP%]{margin-right:6px;margin-left:0}#multisites[_ngcontent-%COMP%]   #evolution[_ngcontent-%COMP%]   .evolution[_ngcontent-%COMP%]{vertical-align:top}#multisites[_ngcontent-%COMP%]   .multisites_asc[_ngcontent-%COMP%]{margin-top:-7px;vertical-align:top}#multisites[_ngcontent-%COMP%]   .multisites_desc[_ngcontent-%COMP%]:after{border-top:5px solid #ccc}#multisites[_ngcontent-%COMP%]   .multisites_asc[_ngcontent-%COMP%]:after, #multisites[_ngcontent-%COMP%]   .multisites_desc[_ngcontent-%COMP%]:after{content:\" \\25BC\";font-size:1px;color:#5793d4;border-left:4px solid transparent;border-right:4px solid transparent}#multisites[_ngcontent-%COMP%]   .multisites_asc[_ngcontent-%COMP%]:after{border-bottom:5px solid #5793d4}#multisites[_ngcontent-%COMP%]   div.sparkline[_ngcontent-%COMP%]{float:none;width:100px;margin:auto}#multisites[_ngcontent-%COMP%]   tfoot[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]{border-bottom:0}.datatable-tr[_ngcontent-%COMP%]{background:#fff;display:table-row;vertical-align:inherit;border-color:inherit}.text-aligh-right[_ngcontent-%COMP%]{text-align:right}.cursor-pointer[_ngcontent-%COMP%]{cursor:pointer}.allWebsitesLoading[_ngcontent-%COMP%]{padding:20px}"] });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(DashboardComponent, [{
                type: i0.Component,
                args: [{
                        selector: 'piwik-dashboard',
                        templateUrl: './dashboard.component.html',
                        styleUrls: ['./dashboard.component.less']
                    }]
            }], function () { return [{ type: DashboardService }]; }, null);
    })();

    var DashboardModule = /** @class */ (function () {
        function DashboardModule() {
        }
        return DashboardModule;
    }());
    DashboardModule.ɵmod = i0.ɵɵdefineNgModule({ type: DashboardModule });
    DashboardModule.ɵinj = i0.ɵɵdefineInjector({ factory: function DashboardModule_Factory(t) { return new (t || DashboardModule)(); }, providers: [DashboardService], imports: [[
                i2$1.CommonModule,
                platformBrowser.BrowserModule,
                i2.RouterModule.forRoot([]),
                i3.FormsModule,
                i1.HttpClientModule,
                i4.CoreHomeModule
            ]] });
    (function () {
        (typeof ngJitMode === "undefined" || ngJitMode) && i0.ɵɵsetNgModuleScope(DashboardModule, { declarations: [DashboardComponent, SiteComponent], imports: [i2$1.CommonModule,
                platformBrowser.BrowserModule, i2.RouterModule, i3.FormsModule,
                i1.HttpClientModule,
                i4.CoreHomeModule], exports: [DashboardComponent] });
    })();
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(DashboardModule, [{
                type: i0.NgModule,
                args: [{
                        declarations: [DashboardComponent, SiteComponent],
                        imports: [
                            i2$1.CommonModule,
                            platformBrowser.BrowserModule,
                            i2.RouterModule.forRoot([]),
                            i3.FormsModule,
                            i1.HttpClientModule,
                            i4.CoreHomeModule
                        ],
                        providers: [DashboardService],
                        exports: [DashboardComponent]
                    }]
            }], null, null);
    })();

    /*
     * Public API Surface of multisites
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.DashboardComponent = DashboardComponent;
    exports.DashboardModule = DashboardModule;
    exports.DashboardService = DashboardService;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=multisites.umd.js.map

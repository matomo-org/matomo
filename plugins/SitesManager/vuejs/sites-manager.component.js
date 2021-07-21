
matomo.registerComponent('matomoSitesManager', {
    name: 'matomoSitesManager',
    data() {
        return {
            availableTypes: {},
            totalNumberOfSites: '?',
            globalSettings: {},
            currencies: {},
            typeForNewEntity: '',
            showAddSiteDialog: false,
            timezoneSupportEnabled: false,
            timezones: {},
            loading: false,
            keepURLFragmentsOptions: {},
            redirectParams: {showaddsite: false},
            hasSuperUserAccess: piwik.hasSuperUserAccess,
            cacheBuster: piwik.cacheBuster,
            currentlyEditedSite: 0,
            period: piwik.broadcast.getValueFromUrl('period'),
            date: piwik.broadcast.getValueFromUrl('date'),
            adminSites: sitesManagerAdminSitesModel,
            sitesManagerTypeModel: piwikHelper.getAngularDependency('sitesManagerTypeModel'),
            coreAPI: piwikHelper.getAngularDependency('coreAPI'),
            sitesManagerAPI: piwikHelper.getAngularDependency('sitesManagerAPI'),
            sitesManagerApiHelper: piwikHelper.getAngularDependency('sitesManagerApiHelper'),
        }
    },
    mounted() {
        var self = this;

        var $rootScope = piwikHelper.getAngularDependency('$rootScope');
        $rootScope.$on('$locationChangeSuccess', function () {
            if (piwik.hasSuperUserAccess) {
                var $window = piwikHelper.getAngularDependency('$window');
                // as we are not using a router yet...
                if ($window.location.hash === '#globalSettings' || $window.location.hash === '#/globalSettings') {
                    broadcast.propagateNewPage('action=globalSettings');
                }
            }
        });

        var init = function () {

            initSelectLists();
            initUserIP();
            initIsTimezoneSupportEnabled();
            initGlobalParams();
        };

        var initAvailableTypes = function () {
            return self.sitesManagerTypeModel.fetchAvailableTypes().then(function (types) {
                self.availableTypes = types;
                self.typeForNewEntity = 'website';

                return types;
            });
        };

        var initSelectLists = function() {

            initCurrencyList();
            initTimezones();
        };

        var initGlobalParams = function() {

            showLoading();

            var availableTypesPromise = initAvailableTypes();

            self.sitesManagerAPI.getGlobalSettings(function(globalSettings) {

                self.globalSettings = globalSettings;

                self.globalSettings.searchKeywordParametersGlobal = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.globalSettings.searchKeywordParametersGlobal);
                self.globalSettings.searchCategoryParametersGlobal = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.globalSettings.searchCategoryParametersGlobal);
                self.globalSettings.excludedIpsGlobal = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.globalSettings.excludedIpsGlobal);
                self.globalSettings.excludedQueryParametersGlobal = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.globalSettings.excludedQueryParametersGlobal);
                self.globalSettings.excludedUserAgentsGlobal = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.globalSettings.excludedUserAgentsGlobal);

                hideLoading();

                initKeepURLFragmentsList();

                self.adminSites.fetchLimitedSitesWithAdminAccess(function () {
                    availableTypesPromise.then(function () {
                        triggerAddSiteIfRequested();
                    });
                });
                self.sitesManagerAPI.getSitesIdWithAdminAccess(function (siteIds) {
                    if (siteIds && siteIds.length) {
                        self.totalNumberOfSites = siteIds.length;
                    }
                });
            });
        };

        var triggerAddSiteIfRequested = function() {
            var search = String(window.location.search);
            var searchParams = piwik.helper.getArrayFromQueryString(search);

            var forcedEditSiteId = self.sitesManagerTypeModel.getEditSiteIdParameter();

            if(searchParams.showaddsite == 1) {
                self.addNewEntity();
            } else if(forcedEditSiteId) {
                self.adminSites.search = parseInt(forcedEditSiteId, 10);
                self.adminSites.searchSite(self.adminSites.search);
            }
        };

        var initIsTimezoneSupportEnabled = function() {

            self.sitesManagerAPI.isTimezoneSupportEnabled(function (timezoneSupportEnabled) {
                self.timezoneSupportEnabled = timezoneSupportEnabled;
            });
        };

        var initTimezones = function() {

            self.sitesManagerAPI.getTimezonesList(

                function (timezones) {

                    var scopeTimezones = [];
                    self.timezones = [];

                    angular.forEach(timezones, function(groupTimezones, timezoneGroup) {

                        angular.forEach(groupTimezones, function(label, code) {

                            scopeTimezones.push({
                                group: timezoneGroup,
                                key: code,
                                value: label
                            });
                        });
                    });

                    self.timezones = scopeTimezones;
                }
            );
        };

        var initUserIP = function() {

            self.coreAPI.getIpFromHeader(function(ip) {
                self.currentIpAddress = ip;
            });
        };

        var initKeepURLFragmentsList = function() {
            self.keepURLFragmentsOptions = [
                {key: 0, value: (self.globalSettings.keepURLFragmentsGlobal ? self.translate('General_Yes') : self.translate('General_No')) + ' (' + self.translate('General_Default') + ')'},
                {key: 1, value: self.translate('General_Yes')},
                {key: 2, value: self.translate('General_No')}
            ];
        };

        var initCurrencyList = function () {

            self.sitesManagerAPI.getCurrencyList(function (currencies) {
                self.currencies = currencies;
            });
        };

        var showLoading = function() {
            self.loading = true;
        };

        var hideLoading = function() {
            self.loading = false;
        };

        init();

    },
    methods: {
        addSite (type) {
            var parameters = {isAllowed: true, measurableType: type};
            var $rootScope = piwikHelper.getAngularDependency('$rootScope');
            $rootScope.$emit('SitesManager.initAddSite', parameters);
            if (parameters && !parameters.isAllowed) {
                return;
            }

            if (!type) {
                type = 'website'; // todo shall we really hard code this or trigger an exception or so?
            }

            this.adminSites.sites.unshift({
                type: type,
                timezone: this.globalSettings.defaultTimezone,
                currency: this.globalSettings.defaultCurrency,
            });
        },
        addNewEntity () {
            var self = this;
            this.sitesManagerTypeModel.hasMultipleTypes().then(function (hasMultipleTypes) {
                if (hasMultipleTypes) {
                    self.showAddSiteDialog = true;
                } else if (self.availableTypes.length === 1) {
                    var type = self.availableTypes[0].id;
                    self.addSite(type);
                }
            });
        },
        saveGlobalSettings () {
            var ajaxHandler = new ajaxHelper();

            ajaxHandler.addParams({
                module: 'SitesManager',
                format: 'json',
                action: 'setGlobalSettings'
            }, 'GET');

            ajaxHandler.addParams({
                timezone: this.globalSettings.defaultTimezone,
                currency: this.globalSettings.defaultCurrency,
                excludedIps: this.globalSettings.excludedIpsGlobal.join(','),
                excludedQueryParameters: this.globalSettings.excludedQueryParametersGlobal.join(','),
                excludedUserAgents: this.globalSettings.excludedUserAgentsGlobal.join(','),
                keepURLFragments: this.globalSettings.keepURLFragmentsGlobal ? 1 : 0,
                searchKeywordParameters: this.globalSettings.searchKeywordParametersGlobal.join(','),
                searchCategoryParameters: this.globalSettings.searchCategoryParametersGlobal.join(',')
            }, 'POST');
            ajaxHandler.withTokenInUrl();
            ajaxHandler.redirectOnSuccess(this.redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send();
        }
    },
    template: `<div class="SitesManager">
    <matomo-content-intro class="sites-manager-header">
        <matomo-enriched-headline
            v-show="availableTypes"
            help-url="https://matomo.org/docs/manage-websites/"
            :feature-name="translate('SitesManager_WebsitesManagement')">
          {{ translate('SitesManager_XManagement', availableTypes && availableTypes.length > 1 ? translate('General_Measurables') : translate('SitesManager_Sites')) }}
        </matomo-enriched-headline>
    
        <p>
            {{ translate('SitesManager_MainDescription') }}

          <span
              v-html="translate('SitesManager_YouCurrentlyHaveAccessToNWebsites', '<strong>' + totalNumberOfSites + '</strong>')"></span>

          <span v-show="hasSuperUserAccess">
                <br/>
                <span
                    v-html="translate('SitesManager_SuperUserAccessCan', '<a href=\\'#globalSettings\\'>', '</a>')"></span>
            </span>
        </p>
    </matomo-content-intro>
    <div :class="{'hide_only': !loading && !adminSites.isLoading}">
      <div class="loadingPiwik">
        <img src="plugins/Morpheus/images/loading-blue.gif" :alt="translate('General_LoadingData')" />
        {{ translate('General_LoadingData') }}
      </div>
    </div>
    
    <div class="sitesButtonBar clearfix" v-show="currentlyEditedSite == 0">

      <a v-show="hasSuperUserAccess && availableTypes"
         class="btn addSite"
         @click="addNewEntity()" tabindex="1">
        {{ availableTypes.length > 1 ? translate('SitesManager_AddMeasurable') : translate('SitesManager_AddSite') }}
      </a>

      <div class="search" v-show="adminSites.hasPrev || adminSites.hasNext || adminSites.searchTerm">
        <input v-model="adminSites.search" v-on:keyup.enter="adminSites.searchSite(adminSites.search)"
               :placeholder="translate('Actions_SubmenuSitesearch')" type="text">
        <img @click="adminSites.searchSite(adminSites.search)" :title="translate('General_ClickToSearch')"
             class="search_ico" src="plugins/Morpheus/images/search_ico.png"/>
      </div>

      <div class="paging" v-show="adminSites.hasPrev || adminSites.hasNext">
        <button class="btn prev" :disabled="!adminSites.hasPrev" @click.prevent="adminSites.previousPage()">
          <span style="cursor:pointer;">&#171; {{ translate('General_Previous') }}</span>
        </button>
        <span class="counter" v-show="adminSites.hasPrev || adminSites.hasNext">
            <span v-if="adminSites.searchTerm">
                {{ translate('General_PaginationWithoutTotal', adminSites.offsetStart, adminSites.offsetEnd) }}
            </span>
            <span v-else>
                {{ translate('General_Pagination', adminSites.offsetStart, adminSites.offsetEnd, totalNumberOfSites) }}
            </span>
        </span>
        <button class="btn next" :disabled="!adminSites.hasNext" @click.prevent="adminSites.nextPage()">
          <span style="cursor:pointer;" class="pointer">{{ translate('General_Next') }} &#187;</span>
        </button>
      </div>

    </div>

    <matomo-dialog :trigger="showAddSiteDialog" @close="showAddSiteDialog = false" class="ui-confirm">

      <h2>{{ translate('SitesManager_ChooseMeasurableTypeHeadline') }}</h2>
      <p />
      <div class="center">
        <button type="button"
                v-for="type in availableTypes"
                :title="type.description"
                class="modal-close btn"
                style="margin-left: 20px;"
                @click="addSite(type.id);"
                aria-disabled="false">
          <span class="ui-button-text">{{ type.name }}</span>
        </button>
      </div>
    </matomo-dialog>

    <div class="sitesManagerList">

      <div ng-for="site in adminSites.sites" ng-include="'plugins/SitesManager/templates/dialogs/dialogs.html?cb=' + cacheBuster"></div>

      <p v-if="adminSites.searchTerm && 0 === adminSites.sites.length && !adminSites.isLoading">
        {{ translate('SitesManager_NotFound') }} <strong>{{ adminSites.searchTerm }}</strong>
      </p>

      <transition-group name="flip-list" tag="div">
        <matomo-sites-manager-site v-show="currentlyEditedSite == site.idsite || currentlyEditedSite == 0" 
                                   v-for="(site, index) in adminSites.sites" 
                                   :data-index="index" :key="site.idsite ?? 0" :matomoSite="site" 
                                   @edit="currentlyEditedSite = site.idsite" @cancel="currentlyEditedSite = 0"></matomo-sites-manager-site>
      </transition-group>

    </div>

    <div class="sitesButtonBar clearfix" v-show="currentlyEditedSite == 0">

      <a v-show="hasSuperUserAccess && availableTypes"
         class="btn addSite"
         @click="addNewEntity()" tabindex="1">
        {{ availableTypes.length > 1 ? translate('SitesManager_AddMeasurable') : translate('SitesManager_AddSite') }}
      </a>

      <div class="search" v-show="adminSites.hasPrev || adminSites.hasNext || adminSites.searchTerm">
        <input v-model="adminSites.search" v-on:keyup.enter="adminSites.searchSite(adminSites.search)"
               :placeholder="translate('Actions_SubmenuSitesearch')" type="text">
        <img @click="adminSites.searchSite(adminSites.search)" :title="translate('General_ClickToSearch')"
             class="search_ico" src="plugins/Morpheus/images/search_ico.png"/>
      </div>

      <div class="paging" v-show="adminSites.hasPrev || adminSites.hasNext">
        <button class="btn prev" :disabled="!adminSites.hasPrev" @click.prevent="adminSites.previousPage()">
          <span style="cursor:pointer;">&#171; {{ translate('General_Previous') }}</span>
        </button>
        <span class="counter" v-show="adminSites.hasPrev || adminSites.hasNext">
            <span v-if="adminSites.searchTerm">
                {{ translate('General_PaginationWithoutTotal', adminSites.offsetStart, adminSites.offsetEnd) }}
            </span>
            <span v-else>
                {{ translate('General_Pagination', adminSites.offsetStart, adminSites.offsetEnd, totalNumberOfSites) }}
            </span>
        </span>
        <button class="btn next" :disabled="!adminSites.hasNext" @click.prevent="adminSites.nextPage()">
          <span style="cursor:pointer;" class="pointer">{{ translate('General_Next') }} &#187;</span>
        </button>
      </div>

    </div>
</div>`
});

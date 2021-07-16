
matomo.VueComponents['matomoSitesManagerSite'] = {
    props: ['matomoSite'],
    data() {
        return {
            site: {},
            currentType: 'website',
            howToSetupUrl : '',
            isInternalSetupUrl: false,
            typeSettings: {},
            measurableSettings: [],
            utcTime: null,
            period: piwik.broadcast.getValueFromUrl('period'),
            date: piwik.broadcast.getValueFromUrl('date'),
            sitesManagerTypeModel: piwikHelper.getAngularDependency('sitesManagerTypeModel'),
            sitesManagerApiHelper: piwikHelper.getAngularDependency('sitesManagerApiHelper'),
            piwikApi: piwikHelper.getAngularDependency('piwikApi'),
            adminSites: sitesManagerAdminSitesModel,
        }
    },
    methods: {
        updateFormField(event) {
            var elem = $(event.target).parents('[piwik-form-field]')

            if (!elem.length) {
                console.error('unable to handle change event for angular form field');
                return;
            }

            console.log(elem.scope());

            var value = elem.scope().field.value;
            var name = elem.scope().field.name;

            if (name) {
                console.log(name, value);
                this.site[name] = value;
            }
        },

        formatDate(format, date) {
            return $.datepicker.formatDate(format, date);
        },
        editSite() {
            var self = this;
            this.site.editMode = true;

            this.measurableSettings = [];
            this.site.isLoading = true;
            self.piwikApi.fetch({
                method: 'SitesManager.getSiteSettings',
                idSite: self.site.idsite
            }).then(function (settings) {
                self.measurableSettings = settings;
                self.site.isLoading = false;
            }, function () {
                self.site.isLoading = false;
            });
        },

        saveSite() {
            var self = this;

            var values = {
                siteName: self.site.name,
                timezone: self.site.timezone,
                currency: self.site.currency,
                type: self.site.type,
                settingValues: {}
            };

            var isNewSite = self.isSiteNew();

            var apiMethod = 'SitesManager.addSite';
            if (!isNewSite) {
                apiMethod = 'SitesManager.updateSite';
                values.idSite = self.site.idsite;
            }

            angular.forEach(self.measurableSettings, function (settings) {
                if (!values['settingValues'][settings.pluginName]) {
                    values['settingValues'][settings.pluginName] = [];
                }

                angular.forEach(settings.settings, function (setting) {
                    var value = setting.value;
                    if (value === false) {
                        value = '0';
                    } else if (value === true) {
                        value = '1';
                    }
                    if (angular.isArray(value) && setting.uiControl == 'textarea') {
                        var newValue = [];
                        angular.forEach(value, function (val) {
                            // as they are line separated we cannot trim them in the view
                            if (val) {
                                newValue.push(val);
                            }
                        });
                        value = newValue;
                    }

                    values['settingValues'][settings.pluginName].push({
                        name: setting.name,
                        value: value
                    });
                });
            });

            self.piwikApi.post({method: apiMethod}, values).then(function (response) {
                self.site.editMode = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();

                var message = _pk_translate('SitesManager_WebsiteUpdated');
                if (isNewSite) {
                    message = _pk_translate('SitesManager_WebsiteCreated');
                }

                notification.show(message, {context: 'success', id: 'websitecreated'});
                notification.scrollToNotification();

                if (!self.site.idsite && response && response.value) {
                    self.site.idsite = response.value;
                }

                angular.forEach(values.settingValues, function (settings, pluginName) {
                    angular.forEach(settings, function (setting) {
                        if (setting.name === 'urls') {
                            self.site.alias_urls = setting.value;
                        } else {
                            self.site[setting.name] = setting.value;
                        }
                    });
                });

                self.sitesManagerTypeModel.removeEditSiteIdParameterFromHash();
            });
        },

        isSiteNew() {
            return angular.isUndefined(this.site.idsite);
        },

        initNewSite() {
            this.site.editMode = true;

            if (this.typeSettings) {
                // we do not want to manipulate initial type settings
                this.measurableSettings = angular.copy(this.typeSettings);
            }
        },

        openDeleteDialog() {

            this.site.removeDialog.title = translate('SitesManager_DeleteConfirm', '"' + this.site.name + '" (idSite = ' + this.site.idsite + ')');
            this.site.removeDialog.show = true;
        },

        deleteSite() {
            var redirectParams = this.redirectParams;

            // if the current idSite in the URL is the site we're deleting, then we have to make to change it. otherwise,
            // if a user goes to another page, the invalid idSite may cause a fatal error.
            if (broadcast.getValueFromUrl('idSite') == $scope.site.idsite) {
                var sites = this.adminSites.sites;

                var otherSite;
                for (var i = 0; i !== sites.length; ++i) {
                    if (sites[i].idsite != $scope.site.idsite) {
                        otherSite = sites[i];
                        break;
                    }
                }

                if (otherSite) {
                    redirectParams = $.extend({}, redirectParams, {idSite: otherSite.idsite});
                }
            }

            var ajaxHandler = new ajaxHelper();

            ajaxHandler.addParams({
                idSite: this.site.idsite,
                module: 'API',
                format: 'json',
                method: 'SitesManager.deleteSite'
            }, 'GET');

            ajaxHandler.redirectOnSuccess(redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send();
        },

        cancelEditSite (site) {
            site.editMode = false;

            var idSite = site.idsite;
            if (idSite) {
                var siteElement = $('.site[idsite=' + idSite + ']');
                if (siteElement[0]) {
                    // todo move this into a directive
                    siteElement[0].scrollIntoView();
                }
            }
            this.sitesManagerTypeModel.removeEditSiteIdParameterFromHash();
        },

        updateView() {
            var $rootScope = piwikHelper.getAngularDependency('$rootScope');
            var $compile = piwikHelper.getAngularDependency('$compile');
            $compile($('[piwik-form-field]:visible,[piwik-field]:visible').not('.ng-isolate-scope'))($rootScope);

            var $timeout = piwikHelper.getAngularDependency('$timeout');
            $timeout(function () {
                $('.editingSite').find('select').material_select();
                Materialize.updateTextFields();
            });
        }
    },
    updated() {
        this.updateView();
    },
    mounted() {
        var self = this;

        function init() {

            self.site = self.matomoSite;

            initModel();
            initActions();
            initUtcTime();

            self.site.isLoading = true;
            self.sitesManagerTypeModel.fetchTypeById(self.site.type).then(function (type) {
                self.site.isLoading = false;

                if (type) {
                    self.currentType = type;
                    self.howToSetupUrl = type.howToSetupUrl;
                    self.isInternalSetupUrl = '?' === ('' + type.howToSetupUrl).substr(0, 1);
                    self.typeSettings = type.settings;

                    if (self.isSiteNew()) {
                        self.measurableSettings = angular.copy(type.settings);
                    }
                } else {
                    self.currentType = {name: self.site.type};
                }

                var forcedEditSiteId = self.sitesManagerTypeModel.getEditSiteIdParameter();
                if (forcedEditSiteId && self.site.idsite == forcedEditSiteId) {
                    self.editSite();
                }
            });
        }

        function initUtcTime() {

            var currentDate = new Date();

            self.utcTime = new Date(
                currentDate.getUTCFullYear(),
                currentDate.getUTCMonth(),
                currentDate.getUTCDate(),
                currentDate.getUTCHours(),
                currentDate.getUTCMinutes(),
                currentDate.getUTCSeconds()
            );
        }

        function initActions() {

            self.site['delete'] = self.deleteSite;
        }

        function initModel() {

            if (self.isSiteNew()) {
                self.initNewSite();
            } else {
                self.site.excluded_ips = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.site.excluded_ips);
                self.site.excluded_parameters = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.site.excluded_parameters);
                self.site.excluded_user_agents = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.site.excluded_user_agents);
                self.site.sitesearch_keyword_parameters = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.site.sitesearch_keyword_parameters);
                self.site.sitesearch_category_parameters = self.sitesManagerApiHelper.commaDelimitedFieldToArray(self.site.sitesearch_category_parameters);
            }

            self.site.removeDialog = {};
        }

        init();
    },
    template: `
<div class="site card hoverable" :idsite="site.idsite" :type="site.type" :class="{'editingSite': site.editMode==true}">
    <div class="card-content">
    
        <div class="row" v-if="!site.editMode">
    
            <div class="col m3">
                <h4>{{ site.name }}</h4>
                <ul>
                    <li><span class="title">{{ translate('General_Id') }}:</span> {{ site.idsite }}</li>
                    <li v-show="$parent.$parent.availableTypes.length > 1"><span class="title">{{ translate('SitesManager_Type') }}:</span> {{ currentType.name }}</li>
                    <li v-show="site.idsite && howToSetupUrl">
                        <a :target="isInternalSetupUrl ? '_self' : '_blank'" :title="translate('SitesManager_ShowTrackingTag')"
                           :href="howToSetupUrl + (isInternalSetupUrl ? '&idSite=' + site.idsite + '&period=' + period + '&date=' + date +'&updated=false' : '')">
                            {{ translate('SitesManager_ShowTrackingTag') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col m4">
                <ul>
                    <li><span class="title">{{ translate('SitesManager_Timezone') }}:</span> {{ site.timezone_name }}</li>
                    <li><span class="title">{{ translate('SitesManager_Currency') }}:</span> {{ site.currency_name }}</li>
                    <li v-show="site.ecommerce == 1">
                        <span class="title">{{ translate('Goals_Ecommerce') }}:</span> {{ translate('General_Yes') }}
                    </li>
                    <li v-show="site.sitesearch == 1">
                        <span class="title">{{ translate('Actions_SubmenuSitesearch') }}:</span> {{ translate('General_Yes') }}
                    </li>
                </ul>
            </div>
            <div class="col m4">
                <ul>
                    <li>
                        <span class="title">{{ translate('SitesManager_Urls') }}</span>:
                        <span v-for="(url, index) in site.alias_urls">
                            <a target=_blank rel="noreferrer noopener" :href="url">{{ url }}{{index == Object.keys(site.alias_urls).length - 1 ? '' : ', '}}</a>
                        </span>
                    </li>
                    <li v-if="site.excluded_ips && site.excluded_ips.length">
                        <span class="title">{{ translate('SitesManager_ExcludedIps') }}:</span>
                        {{ site.excluded_ips.join(', ') }}
                    </li>
                    <li v-if="site.excluded_parameters && site.excluded_parameters.length">
                        <span class="title">{{ translate('SitesManager_ExcludedParameters') }}:</span>
                        {{ site.excluded_parameters.join(', ') }}
                    </li>
                    <li v-if="site.excluded_user_agents && site.excluded_user_agents.length">
                        <span class="title">{{ translate('SitesManager_ExcludedUserAgents') }}:</span>
                        {{ site.excluded_user_agents.join(', ') }}
                    </li>
                </ul>
            </div>
            <div class="col m1 text-right">
                <ul>
                    <li>
                        <button class="table-action" @click="editSite()" :title="translate('General_Edit')">
                            <span class="icon-edit"></span>
                        </button>
                    </li>
                    <li>
                        <button class="table-action" v-show="site.idsite" @click="openDeleteDialog()" :title="translate('General_Delete')">
                            <span class="icon-delete"></span>
                        </button>
                    </li>
                </ul>
            </div>
    
        </div>
    
        <div v-if="site.editMode">
    
            <div class="form-group row">
                <div class="col s12 m6 input-field">
                    <input type="text" v-model="site.name" maxlength="90" :placeholder="translate('General_Name')" />
                    <label>{{ translate('General_Name') }}</label>
                </div>
                <div class="col s12 m6"></div>
            </div>
    
            <matomoActivityIndicator :loading="site.isLoading"></matomoActivityIndicator>
    
            <div v-for="settingsPerPlugin in measurableSettings">
                <div v-for="setting in settingsPerPlugin.settings" 
                     :piwik-form-field="JSON.stringify(setting)" 
                     :all-settings="JSON.stringify(settingsPerPlugin.settings)" 
                     @change="updateFormField"></div>
            </div>
    
            <div piwik-field uicontrol="select" name="currency" @change="updateFormField"
                 :value="site.currency"
                 :title="translate('SitesManager_Currency')"
                 :inline-help="translate('SitesManager_CurrencySymbolWillBeUsedForGoals')"
                 :options="JSON.stringify($parent.$parent.currencies)">
            </div>
    
            <div piwik-field uicontrol="select" name="timezone" @change="updateFormField"
                 :value="site.timezone"
                 :title="translate('SitesManager_Timezone')"
                 inline-help="#timezoneHelpText"
                 :options="JSON.stringify($parent.$parent.timezones)">
            </div>
    
            <div id="timezoneHelpText" class="inline-help-node">
                <span v-if="!$parent.$parent.timezoneSupportEnabled">
                    {{ translate('SitesManager_AdvancedTimezoneSupportNotFound') }}
                  <br/>
                </span>

                {{ translate('SitesManager_UTCTimeIs', formatDate('yy-M-dd HH:mm:ss', utcTime)) }}
                <br/>
                {{ translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward') }}
            </div>
    
            <div class="editingSiteFooter">
                <input v-show="!site.isLoading" type="submit" class="btn" :value="translate('General_Save')" @click="saveSite()"/>
                <button class="btn btn-link" @click="cancelEditSite(site)">{{ translate('General_Cancel', '', '') }}</button>
            </div>
    
        </div>
    </div>
</div>`
};

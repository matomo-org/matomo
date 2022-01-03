<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="site card hoverable"
    :idsite="theSite.idsite"
    :type="theSite.type"
    :class="{ 'editingSite': !!editMode }"
  >
    <div class="card-content">
      <div class="row" v-if="!editMode">
        <div class="col m3">
          <h4>{{ theSite.name }}</h4>
          <ul>
            <li><span class="title">{{ translate('General_Id') }}:</span> {{ theSite.idsite }}</li>
            <li v-show="availableTypes.length > 1">
              <span class="title">{{ translate('SitesManager_Type') }}:</span>
              {{ currentType.name }}
            </li>
            <li v-show="theSite.idsite && howToSetupUrl">
              <a
                :target="isInternalSetupUrl ? '_self' : '_blank'"
                :title="translate('SitesManager_ShowTrackingTag')"
                :href="setupUrl"
              >
                {{ translate('SitesManager_ShowTrackingTag') }}
              </a>
            </li>
          </ul>
        </div>
        <div class="col m4">
          <ul>
            <li>
              <span class="title">{{ translate('SitesManager_Timezone') }}:</span>
              {{ theSite.timezone_name }}
            </li>
            <li>
              <span class="title">{{ translate('SitesManager_Currency') }}:</span>
              {{ theSite.currency_name }}
            </li>
            <li v-show="theSite.ecommerce === 1">
              <span class="title">{{ translate('Goals_Ecommerce') }}:</span>
              {{ translate('General_Yes') }}
            </li>
            <li v-show="theSite.sitesearch == 1">
              <span class="title">{{ translate('Actions_SubmenuSitesearch') }}:</span>
              {{ translate('General_Yes') }}
            </li>
          </ul>
        </div>
        <div class="col m4">
          <ul>
            <li>
              <span class="title">{{ translate('SitesManager_Urls') }}</span>:
              <span v-for="(url, index) in theSite.alias_urls" :key="url">
                  <a target=_blank rel="noreferrer noopener" :href="url">
                    {{ url }}{{ index === theSite.alias_urls.length - 1 ? '' : ', ' }}
                  </a>
              </span>
            </li>
            <li v-show="theSite.excluded_ips.length">
              <span class="title">{{ translate('SitesManager_ExcludedIps') }}:</span>
              {{ theSite.excluded_ips.join(', ') }}
            </li>
            <li v-show="theSite.excluded_parameters.length">
              <span class="title">{{ translate('SitesManager_ExcludedParameters') }}:</span>
              {{ theSite.excluded_parameters.join(', ') }}
            </li>
            <li v-if="theSite.excluded_user_agents.length">
              <span class="title">{{ translate('SitesManager_ExcludedUserAgents') }}:</span>
              {{ theSite.excluded_user_agents.join(', ') }}
            </li>
          </ul>
        </div>
        <div class="col m1 text-right">
          <ul>
            <li>
              <button
                class="table-action"
                @click="editSite()"
                :title="translate('General_Edit')"
              >
                <span class="icon-edit"></span>
              </button>
            </li>
            <li>
              <button
                class="table-action"
                v-show="theSite.idsite"
                @click="this.showRemoveDialog = true"
                :title="translate('General_Delete')"
              >
                <span class="icon-delete"></span>
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div v-if="editMode">

        <div class="form-group row">
          <div class="col s12 m6 input-field">
            <input
              type="text"
              v-model="theSite.name"
              maxlength="90"
              :placeholder="translate('General_Name')"
            />
            <label>{{ translate('General_Name') }}</label>
          </div>
          <div class="col s12 m6"></div>
        </div>

        <ActivityIndicator :loading="isLoading"/>

        <div v-for="settingsPerPlugin in measurableSettings" :key="settingsPerPlugin.plugin">
          <div
            v-for="setting in settingsPerPlugin.settings"
            :key="`${settings.pluginName}.${setting.name}`"
          >
            <PluginSetting
              v-model="settingValues[`${settings.pluginName}.${setting.name}`]"
              :plugin-name="settingsPerPlugin.pluginName"
              :setting="setting"
              :setting-values="settingValues"
            />
          </div>
        </div>

        <Field
          uicontrol="select"
          name="currency"
          v-model="theSite.currency"
          :title="translate('SitesManager_Currency')"
          :inline-help="translate('SitesManager_CurrencySymbolWillBeUsedForGoals')"
          :options="currencies"
        />

        <Field
          uicontrol="select"
          name="timezone"
          v-model="site.timezone"
          :title="translate('SitesManager_Timezone')"
          :inline-help="'#timezoneHelpText'"
          :options="timezones"
        />

        <div id="timezoneHelpText" class="inline-help-node">
          <div>
            <span v-if="!timezoneSupportEnabled">
              {{ translate('SitesManager_AdvancedTimezoneSupportNotFound') }}
              <br/>
            </span>

            {{ utcTimeIs }}
            <br/>
            {{ translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward') }}
          </div>
        </div>

        <div class="editingSiteFooter">
          <input
            v-show="!isLoading"
            type="submit"
            class="btn"
            :value="translate('General_Save')"
            @click="saveSite()"
          />
          <button
            class="btn btn-link"
            @click="cancelEditSite(site)"
          >
            {{ translate('General_Cancel', '', '') }}
          </button>
        </div>

      </div>
    </div>

    <MatomoDialog
      class="ui-confirm"
      v-model="showRemoveDialog"
      @yes="deleteSite()"
    >
      <h2>{{ removeDialogTitle }}</h2>

      <p>{{ translate('SitesManager_DeleteSiteExplanation') }}</p>

      <input type="button" :value="translate('General_Yes')" role="yes"/>
      <input type="button" :value="translate('General_No')" role="no" />
    </MatomoDialog>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from 'vue';
// TODO: rename format to formatDate
import {
  Site,
  MatomoUrl,
  ActivityIndicator,
  format,
  translate,
  MatomoDialog,
  AjaxHelper,
  NotificationsStore,
} from 'CoreHome';
import {
  Field,
  PluginSetting,
  SettingsForSinglePlugin,
  Setting,
} from 'CorePluginsAdmin';
import TimezoneStore from '../TimezoneStore/TimezoneStore';
import CurrencyStore from '../CurrencyStore/CurrencyStore';
import SiteTypesStore from '../SiteTypesStore/SiteTypesStore';
import SiteType from "../SiteTypesStore/SiteType";

interface SiteFieldsState {
  isLoading: boolean;
  editMode: boolean;
  theSite: Site;
  measurableSettings: SettingsForSinglePlugin[];
  settingValues: Record<string, unknown>;
  showRemoveDialog: boolean;
}

interface Option {
  group: string;
  key: string;
  value: string;
}

interface CreateEditSiteResponse {
  value: string|number;
}

// TODO: double check this is done lazily.
let timezoneOptions = computed(() => {
  return TimezoneStore.timezones.value.map(({ group, label, code }) => ({
    group,
    key: label,
    value: code,
  }));
});

function isSiteNew(site: Site) {
  return typeof site.idsite === 'undefined';
}

export default defineComponent({
  props: {
    site: {
      type: Object,
      required: true,
    },
    timezoneSupportEnabled: {
      type: Boolean,
      required: true,
    },
    utcTime: {
      type: Date,
      required: true,
    },
    globalSettings: {
      type: Object,
      required: true,
    },
  },
  data(): SiteFieldsState {
    return {
      isLoading: false,
      editMode: false,
      theSite: { ...(this.site as Site) },
      measurableSettings: [],
      settingValues: {},
      showRemoveDialog: false,
    };
  },
  components: {
    MatomoDialog,
    Field,
    PluginSetting,
    ActivityIndicator,
  },
  emits: ['delete', 'cancelEditSite'],
  created() {
    this.onSiteChanged();
  },
  watch: {
    site() {
      this.onSiteChanged();
    },
    measurableSettings(settings: SettingsForSinglePlugin[]) {
      if (!settings.length) {
        return;
      }

      const settingValues = {};
      settings.forEach((settingsForPlugin) => {
        settingsForPlugin.settings.forEach((setting) => {
          settingValues[`${settingsForPlugin.pluginName}.${setting.name}`] = setting.value;
        });
      });
      this.settingValues = settingValues;
    },
  },
  methods: {
    onSiteChanged() {
      const site = this.site as Site;
      const isSiteNew = isSiteNew(site);

      if (isSiteNew) {
        const globalSettings = this.globalSettings as Record<string, string>;
        this.theSite.timezone = globalSettings.defaultTimezone;
        this.theSite.currency = globalSettings.defaultCurrency;
      }

      const forcedEditSiteId = SiteTypesStore.getEditSiteIdParameter();
      if (isSiteNew
        || (forcedEditSiteId && `${site.idsite}` === forcedEditSiteId)
      ) {
        // make sure type info is available before entering edit mode
        SiteTypesStore.fetchAvailableTypes().then(() => {
          this.editSite();
        });
      }
    },
    editSite() {
      this.editMode = true;

      this.measurableSettings = [];

      if (isSiteNew(this.theSite)) {
        if (!this.currentType) {
          return;
        }

        this.measurableSettings = this.currentType.settings || [];
        return;
      }

      this.isLoading = true;
      AjaxHelper.fetch<SettingsForSinglePlugin[]>({
        method: 'SitesManager.getSiteSettings',
        idSite: this.theSite.idsite,
      }).then((settings) => {
        this.measurableSettings = settings;
      }).finally(() => {
        this.isLoading = false;
      });
    },
    saveSite() {
      const values: QueryParameters = {
        siteName: this.theSite.name,
        timezone: this.theSite.timezone,
        currency: this.theSite.currency,
        type: this.theSite.type,
        settingValues: {} as Record<string, Setting[]>,
      };

      const isSiteNew = isSiteNew(this.theSite);

      let apiMethod = 'SitesManager.addSite';
      if (!isSiteNew) {
        apiMethod = 'SitesManager.updateSite';
        values.idSite = this.theSite.idsite;
      }

      // process measurable settings
      Object.entries(this.settingValues).forEach(([fullName, fieldValue]) => {
        const [pluginName, name] = fullName.split('.');

        if (!values.settingValues[pluginName]) {
          values.settingValues[pluginName] = [];
        }

        let value = fieldValue;
        if (fieldValue === false) {
          value = '0';
        } else if (fieldValue === true) {
          value = '1';
        } else if (Array.isArray(fieldValue)) {
          value = fieldValue.filter((x) => !!x);
        }

        values.settingValues[pluginName].push({
          name,
          value,
        });
      });

      AjaxHelper.post<CreateEditSiteResponse>(
        {
          method: apiMethod,
        },
        values,
      ).then((response) => {
        this.editMode = false;

        if (!this.theSite.idsite && response && response.value) {
          this.theSite.idsite = response.value;
        }

        const notificationId = NotificationsStore.show({
          message: isSiteNew
            ? translate('SitesManager_WebsiteCreated')
            : translate('SitesManager_WebsiteUpdated'),
          context: 'success',
          id: 'websitecreated',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationId);

        SiteTypesStore.removeEditSiteIdParameterFromHash();

        this.$emit('save', this.theSite);
      });
      /*
      TODO: not sure if this code is needed.
            piwikApi.post({method: apiMethod}, values).then(function (response) {
                angular.forEach(values.settingValues, function (settings, pluginName) {
                    angular.forEach(settings, function (setting) {
                        if (setting.name === 'urls') {
                            $scope.site.alias_urls = setting.value;
                        } else {
                            $scope.site[setting.name] = setting.value;
                        }
                    });
                });
            });
       */
    },
    cancelEditSite(site: Site) {
      this.editMode = false;

      // TODO: double check if needed to keep this method in the store
      SiteTypesStore.removeEditSiteIdParameterFromHash();

      this.$emit('cancelEditSite', site);
    },
    deleteSite() {
      AjaxHelper.fetch({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.deleteSite',
      }).then(() => {
        this.$emit('delete', this.theSite);
      });
    },
  },
  computed: {
    availableTypes() {
      return SiteTypesStore.typesById.value;
    },
    setupUrl() {
      const site = this.theSite as Site;

      let suffix = '';
      if (this.isInternalSetupUrl) {
        suffix = MatomoUrl.stringify({
          idSite: site.idsite,
          period: MatomoUrl.parsed.value.period,
          date: MatomoUrl.parsed.value.date,
          updated: 'false',
        });
      }
      return `${this.howToSetupUrl}${suffix}`;
    },
    utcTimeIs() {
      // TODO: check the time works properly
      const date = format(this.utcTime, 'yy-mm-dd hh:mm:ss');
      return translate('SitesManager_UTCTimeIs', date);
    },
    timezones() {
      return timezoneOptions.value;
    },
    currencies() {
      return CurrencyStore.currencies.value;
    },
    currentType(): SiteType {
      const type = SiteTypesStore.typesById[this.site.type];
      if (!type) {
        return { name: this.site.type } as SiteType;
      }
      return type;
    },
    howToSetupUrl() {
      const type = this.currentType;
      if (!type) {
        return undefined;
      }

      return type.howToSetupUrl;
    },
    isInternalSetupUrl() {
      const { howToSetupUrl } = this;
      if (!howToSetupUrl) {
        return false;
      }

      return '?' === (`${howToSetupUrl}`).substring(0, 1);
    },
    removeDialogTitle() {
      return translate(
        'SitesManager_DeleteConfirm',
        `"${this.theSite.name}" (idSite = ${this.theSite.idsite})`,
      );
    }
  },
});
</script>

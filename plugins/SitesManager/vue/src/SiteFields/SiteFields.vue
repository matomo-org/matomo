<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="site card hoverable"
    :idsite="theSite.idsite"
    :type="theSite.type"
    :class="{ 'editingSite': editMode }"
    ref="root"
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
            <li v-show="theSite.ecommerce === 1 || theSite.ecommerce === '1'">
              <span class="title">{{ translate('Goals_Ecommerce') }}:</span>
              {{ translate('General_Yes') }}
            </li>
            <li v-show="theSite.sitesearch === 1 || theSite.sitesearch === '1'">
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
            <li v-if="theSite.excluded_ips?.length">
              <span class="title">{{ translate('SitesManager_ExcludedIps') }}:</span>
              {{ theSite.excluded_ips.split(/\s*,\s*/g).join(', ') }}
            </li>
            <li v-if="theSite.excluded_parameters?.length">
              <span class="title">{{ translate('SitesManager_ExcludedParameters') }}:</span>
              {{ theSite.excluded_parameters.split(/\s*,\s*/g).join(', ') }}
            </li>
            <li v-if="theSite.excluded_user_agents?.length">
              <span class="title">{{ translate('SitesManager_ExcludedUserAgents') }}:</span>
              {{ theSite.excluded_user_agents.split(/\s*,\s*/g).join(', ') }}
            </li>
          </ul>
        </div>
        <div class="col m1 right-align">
          <button
            class="table-action"
            @click="editSite()"
            :title="translate('General_Edit')"
          >
            <span class="icon-edit"></span>
          </button>
          <button
            class="table-action"
            v-show="theSite.idsite"
            @click="getMessagesToWarnOnSiteRemoval()"
            :title="translate('General_Delete')"
          >
            <span class="icon-delete"></span>
          </button>
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

        <div v-for="settingsPerPlugin in measurableSettings" :key="settingsPerPlugin.pluginName">
          <GroupedSettings
            :group-name="settingsPerPlugin.pluginName"
            :settings="settingsPerPlugin.settings"
            :all-setting-values="settingValues"
            @change="settingValues[`${settingsPerPlugin.pluginName}.${$event.name}`] = $event.value"
          />
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
          v-model="theSite.timezone"
          :title="translate('SitesManager_Timezone')"
          :inline-help="`#timezoneHelpText-${theSite.idsite}`"
          :options="timezones"
        />

        <div :id="`timezoneHelpText-${theSite.idsite}`" class="inline-help-node">
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

        <template v-if="privacyManagerEnabled && theSite && theSite.idsite">
          <h3 class="">{{ translate('PrivacyManager_TrackingDataAnonymizationSettings') }}</h3>

          <ActivityIndicator :loading="isLoadingPrivacy"/>

          <component
            :is="anonymizeIpComponent"
            v-if="!isLoadingPrivacy"
            :id-site-specific="theSite.idsite"
            :trigger-save="triggerSavePrivacySettings == 'save'"
            v-bind="anonymisationSettings"
            @updated="onPrivacyUpdated"
            @aborted="onPrivacyAborted"
            @cancel="cancelEditSite(site)"
          ></component>
        </template>

        <div class="editingSiteFooter">
          <input
            v-show="!isLoading"
            :disabled="isSaving"
            type="submit"
            class="btn"
            :value="translate('General_Save')"
            @click="saveSite()"
          />
          <button
            class="btn btn-link"
            :disabled="isSaving"
            @click="cancelEditSite(site)"
          >
            {{ translate('General_Cancel', '', '') }}
          </button>
        </div>

      </div>
    </div>

    <PasswordConfirmation
      v-model="showRemoveDialog"
      @confirmed="deleteSite"
      :password-field-id="'currentUserPassword-'+theSite.idsite"
    >
        <h2>{{ removeDialogTitle }}</h2>
        <p>{{ translate('SitesManager_DeleteSiteExplanation') }}</p>
        <p v-if="deleteSiteExplanation" v-html="$sanitize(deleteSiteExplanation)"></p>
    </PasswordConfirmation>
  </div>
</template>

<script lang="ts">
import { computed, DeepReadonly, defineComponent } from 'vue';
import {
  Site,
  MatomoUrl,
  ActivityIndicator,
  format,
  translate,
  AjaxHelper,
  NotificationsStore,
  useExternalPluginComponent,
} from 'CoreHome';
import {
  Field,
  GroupedSettings,
  SettingsForSinglePlugin,
  Setting,
  PasswordConfirmation,
} from 'CorePluginsAdmin';
import TimezoneStore from '../TimezoneStore/TimezoneStore';
import CurrencyStore from '../CurrencyStore/CurrencyStore';
import SiteTypesStore from '../SiteTypesStore/SiteTypesStore';
import SiteType from '../SiteTypesStore/SiteType';

interface SiteFieldsState {
  isLoading: boolean;
  isLoadingPrivacy: boolean;
  isSaving: boolean;
  editMode: boolean;
  theSite: Site;
  measurableSettings: DeepReadonly<SettingsForSinglePlugin[]>;
  anonymisationSettings: DeepReadonly<SettingsForSinglePlugin[]>;
  settingValues: Record<string, unknown>;
  showRemoveDialog: boolean;
  deleteSiteExplanation: string;
  triggerSavePrivacySettings: string;
}

interface CreateEditSiteResponse {
  value: string|number;
}

const timezoneOptions = computed(
  () => TimezoneStore.timezones.value.map(({ group, label, code }) => ({
    group,
    key: label,
    value: code,
  })),
);

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
    },
    utcTime: {
      type: Date,
      required: true,
    },
    globalSettings: {
      type: Object,
      required: true,
    },
    privacyManagerEnabled: {
      type: Boolean,
      default: false,
    },
  },
  data(): SiteFieldsState {
    return {
      isLoading: false,
      isLoadingPrivacy: false,
      isSaving: false,
      editMode: false,
      theSite: { ...(this.site as Site) },
      measurableSettings: [],
      anonymisationSettings: [],
      settingValues: {},
      showRemoveDialog: false,
      deleteSiteExplanation: '',
      triggerSavePrivacySettings: '',
    };
  },
  components: {
    PasswordConfirmation,
    Field,
    GroupedSettings,
    ActivityIndicator,
  },
  emits: ['delete', 'editSite', 'cancelEditSite', 'save'],
  created() {
    CurrencyStore.init();
    TimezoneStore.init();
    SiteTypesStore.init();

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

      const settingValues: Record<string, unknown> = {};
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

      this.theSite = { ...site };

      const isNew = isSiteNew(site);

      if (isNew) {
        const globalSettings = this.globalSettings as Record<string, string>;
        this.theSite.timezone = globalSettings.defaultTimezone;
        this.theSite.currency = globalSettings.defaultCurrency;
      }

      const forcedEditSiteId = SiteTypesStore.getEditSiteIdParameter();
      if (isNew
        || (forcedEditSiteId && `${site.idsite}` === forcedEditSiteId)
      ) {
        this.editSite();
      }
    },
    editSite() {
      this.editMode = true;

      const idSite = this.theSite.idsite;
      this.$emit('editSite', { idSite });

      this.measurableSettings = [];
      this.anonymisationSettings = [];

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
        idSite,
      }).then((settings) => {
        this.measurableSettings = settings;
      }).finally(() => {
        this.isLoading = false;
      });

      if (this.privacyManagerEnabled && idSite) {
        this.isLoadingPrivacy = true;
        AjaxHelper.fetch<SettingsForSinglePlugin[]>({
          method: 'PrivacyManager.getAnonymisationSettings',
          idSiteSpecific: idSite,
        })
          .then((settings) => {
            this.anonymisationSettings = settings;
          })
          .finally(() => {
            this.isLoadingPrivacy = false;
          });
      }
    },
    onPrivacyUpdated() {
      this.triggerSavePrivacySettings = 'done';
      this.anonymisationSettings = [];
    },
    onPrivacyAborted() {
      this.triggerSavePrivacySettings = 'abort';
      this.isSaving = false;
    },
    saveSite() {
      if (this.isSaving) {
        return; // saving already in progress
      }

      this.isSaving = true;

      const values: Record<string, unknown> = {
        siteName: this.theSite.name,
        timezone: this.theSite.timezone,
        currency: this.theSite.currency,
        type: this.theSite.type,
        settingValues: {} as Record<string, Setting[]>,
      };

      const isNew = isSiteNew(this.theSite);

      let apiMethod = 'SitesManager.addSite';
      if (!isNew) {
        apiMethod = 'SitesManager.updateSite';
        values.idSite = this.theSite.idsite;
      }

      // process measurable settings
      Object.entries(this.settingValues).forEach(([fullName, fieldValue]) => {
        const [pluginName, name] = fullName.split('.');

        const settingValues = values.settingValues as Record<string, Setting[]>;
        if (!settingValues[pluginName]) {
          settingValues[pluginName] = [];
        }

        let value = fieldValue;
        if (fieldValue === false) {
          value = '0';
        } else if (fieldValue === true) {
          value = '1';
        } else if (Array.isArray(fieldValue)) {
          value = fieldValue.filter((x) => !!x);
        }

        settingValues[pluginName].push({
          name,
          value,
        });
      });

      const showNotificationAndEmitSave = () => {
        const notificationId = NotificationsStore.show({
          message: isNew
            ? translate('SitesManager_WebsiteCreated')
            : translate('SitesManager_WebsiteUpdated'),
          context: 'success',
          id: 'websitecreated',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationId);

        SiteTypesStore.removeEditSiteIdParameterFromHash();

        this.isSaving = false;
        this.editMode = false;

        this.$emit('save', {
          site: this.theSite,
          settingValues: values.settingValues,
          isNew,
        });
      };

      const saveSitePromise = () => Promise
        .resolve(AjaxHelper.post<CreateEditSiteResponse>(
          {
            method: apiMethod,
          },
          values,
        )).then((response) => {
          if (!this.theSite.idsite && response && response.value) {
            this.theSite.idsite = `${response.value}`;
          }

          const timezoneInfo = TimezoneStore.timezones.value.find(
            (t) => t.code === this.theSite.timezone,
          );
          this.theSite.timezone_name = timezoneInfo?.label || this.theSite.timezone;

          if (this.theSite.currency) {
            this.theSite.currency_name = CurrencyStore.currencies.value[this.theSite.currency];
          }
        });

      if (!isNew) {
        const savePrivacySettingsPromise = this.getTriggerPrivacySettingsSavePromise();
        savePrivacySettingsPromise.then(
          () => saveSitePromise().then(() => {
            showNotificationAndEmitSave();
          }).catch(() => {
            // enable saving again on error
            this.isSaving = false;
          }),
        ).catch(() => {
          // on privacy settings password confirmation abort or wrong password
          this.isSaving = false;
        });
      } else {
        saveSitePromise().then(() => {
          showNotificationAndEmitSave();
        });
      }
    },
    cancelEditSite(site: Site) {
      this.editMode = false;

      SiteTypesStore.removeEditSiteIdParameterFromHash();

      this.$emit('cancelEditSite', { site, element: this.$refs.root as HTMLElement });
    },
    deleteSite(password: string) {
      AjaxHelper.post({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.deleteSite',
      }, {
        passwordConfirmation: password,
      }).then(() => {
        this.$emit('delete', this.theSite);
      });
    },
    getMessagesToWarnOnSiteRemoval() {
      AjaxHelper.post({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.getMessagesToWarnOnSiteRemoval',
      }).then((response) => {
        this.deleteSiteExplanation = '';
        if (response.length) {
          this.deleteSiteExplanation += response.join('<br>');
        }
        this.showRemoveDialog = true;
      });
    },
    getTriggerPrivacySettingsSavePromise() {
      return new Promise((resolve, reject) => {
        const unwatchTrigger = this.$watch(
          'triggerSavePrivacySettings',
          (val) => {
            if (val === 'done') {
              unwatchTrigger();
              resolve(true);
            }
            if (val === 'abort') {
              unwatchTrigger();
              reject();
            }
          },
          { immediate: false },
        );
        this.triggerSavePrivacySettings = 'save';
      });
    },
  },
  computed: {
    availableTypes() {
      return SiteTypesStore.types.value;
    },
    setupUrl() {
      const site = this.theSite as Site;

      let suffix = '';
      let connector = '';
      if (this.isInternalSetupUrl) {
        suffix = MatomoUrl.stringify({
          idSite: site.idsite,
          period: MatomoUrl.parsed.value.period,
          date: MatomoUrl.parsed.value.date,
          updated: 'false',
        });

        connector = this.howToSetupUrl!.indexOf('?') === -1 ? '?' : '&';
      }
      return `${this.howToSetupUrl}${connector}${suffix}`;
    },
    utcTimeIs() {
      const utcTime = this.utcTime as Date;

      const formatTimePart = (n: number) => n.toString().padStart(2, '0');

      const hours = formatTimePart(utcTime.getHours());
      const minutes = formatTimePart(utcTime.getMinutes());
      const seconds = formatTimePart(utcTime.getSeconds());

      const date = `${format(this.utcTime)} ${hours}:${minutes}:${seconds}`;
      return translate('SitesManager_UTCTimeIs', date);
    },
    timezones() {
      return timezoneOptions.value;
    },
    currencies() {
      return CurrencyStore.currencies.value;
    },
    currentType(): DeepReadonly<SiteType> {
      const site = this.site as Site;
      const type = SiteTypesStore.typesById.value[site.type];
      if (!type) {
        return { name: site.type } as SiteType;
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

      return (`${howToSetupUrl}`).substring(0, 1) === '?';
    },
    removeDialogTitle() {
      return translate(
        'SitesManager_DeleteConfirm',
        `"${this.theSite.name}" (idSite = ${this.theSite.idsite})`,
      );
    },
    anonymizeIpComponent() {
      if (this.privacyManagerEnabled) {
        return useExternalPluginComponent('PrivacyManager', 'AnonymizeIp');
      }
      return '';
    },
  },
});
</script>

<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="SitesManager">
    <ContentBlock
      v-show="hasSuperUserAccess"
      :content-title="translate('SitesManager_GlobalWebsitesSettings')"
    >
      <a name="globalSettings" id="globalSettings"></a>

      <div id="excludedIpsGlobalHelp" class="inline-help-node">
        <div>
          {{ translate(
              'SitesManager_HelpExcludedIpAddresses',
              '1.2.3.4/24',
              '1.2.3.*',
              '1.2.*.*',
            ) }}

          <br/><br/>

          <span v-html="$sanitize(yourCurrentIpAddressIs)"></span>
        </div>
      </div>

      <div id="excludedUserAgentsGlobalHelp" class="inline-help-node">
        <div>
          {{ translate('SitesManager_GlobalExcludedUserAgentHelp1') }}

          <br/><br/>

          {{ translate('SitesManager_GlobalListExcludedUserAgents_Desc') }}
          {{ translate('SitesManager_GlobalExcludedUserAgentHelp2') }}
          {{ translate(
            'SitesManager_GlobalExcludedUserAgentHelp3',
            '/bot|spider|crawl|scanner/i',
            ) }}
        </div>
      </div>

      <div id="excludedReferrersGlobalHelp" class="inline-help-node">
        <div>
          {{ translate('SitesManager_ExcludedReferrersHelp') }}
          <br/><br/>
          {{ translate('SitesManager_ExcludedReferrersHelpDetails') }}
          <br/>
          {{ translate(
              'SitesManager_ExcludedReferrersHelpExamples',
              'www.example.org',
              'http://example.org/mypath',
              'https://www.example.org/?param=1',
              'https://sub.example.org/'
          ) }}
          <br/><br/>
          {{ translate(
            'SitesManager_ExcludedReferrersHelpSubDomains',
            '.sub.example.org',
            'http://sub.example.org/mypath',
            'https://new.sub.example.org/'
          ) }}
        </div>
      </div>

      <div id="timezoneHelp" class="inline-help-node">
        <div>
          <span v-if="!timezoneSupportEnabled">
              {{ translate('SitesManager_AdvancedTimezoneSupportNotFound') }}
              <br/>
          </span>

          {{ translate('SitesManager_UTCTimeIs', utcTimeDate) }}
          <br/>
          {{ translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward') }}
        </div>
      </div>

      <div id="keepURLFragmentsHelp" class="inline-help-node">
        <div v-html="$sanitize(keepUrlFragmentHelp)"></div>
        <div>{{ translate('SitesManager_KeepURLFragmentsHelp2') }}</div>
      </div>

      <div>
        <Field
          uicontrol="textarea"
          name="excludedIpsGlobal"
          var-type="array"
          v-model="excludedIpsGlobal"
          :title="translate('SitesManager_ListOfIpsToBeExcludedOnAllWebsites')"
          :introduction="translate('SitesManager_GlobalListExcludedIps')"
          :inline-help="'#excludedIpsGlobalHelp'"
          :disabled="isLoading"
        />
      </div>

      <ExcludeQueryParameterSettings
        v-model:exclusionTypeForQueryParams="exclusionTypeForQueryParams"
        v-model:excludedQueryParametersGlobal="excludedQueryParametersGlobal"
        :commonSensitiveQueryParams="commonSensitiveQueryParams"
      />

      <div>
        <Field
          uicontrol="textarea"
          name="excludedUserAgentsGlobal"
          var-type="array"
          v-model="excludedUserAgentsGlobal"
          :title="translate('SitesManager_GlobalListExcludedUserAgents_Desc')"
          :introduction="translate('SitesManager_GlobalListExcludedUserAgents')"
          :inline-help="'#excludedUserAgentsGlobalHelp'"
          :disabled="isLoading"
        />
      </div>

      <div>
        <Field
          uicontrol="textarea"
          name="excludedReferrersGlobal"
          var-type="array"
          v-model="excludedReferrersGlobal"
          :title="translate('SitesManager_GlobalListExcludedReferrersDesc')"
          :introduction="translate('SitesManager_GlobalListExcludedReferrers')"
          :inline-help="'#excludedReferrersGlobalHelp'"
          :disabled="isLoading"
        />
      </div>

      <div>
        <Field
          uicontrol="checkbox"
          name="keepURLFragmentsGlobal"
          v-model="keepURLFragmentsGlobal"
          :title="translate('SitesManager_KeepURLFragmentsLong')"
          :introduction="translate('SitesManager_KeepURLFragments')"
          :inline-help="'#keepURLFragmentsHelp'"
          :disabled="isLoading"
        />
      </div>

      <h3>{{ translate('SitesManager_TrackingSiteSearch') }}</h3>

      <p>{{ translate('SitesManager_SiteSearchUse') }}</p>
      <div class="alert alert-info">
        {{ translate('SitesManager_SearchParametersNote') }}
        {{ translate('SitesManager_SearchParametersNote2') }}
      </div>

      <div>
        <Field
          uicontrol="text"
          name="searchKeywordParametersGlobal"
          var-type="array"
          v-model="searchKeywordParametersGlobal"
          :title="translate('SitesManager_SearchKeywordLabel')"
          :inline-help="translate('SitesManager_SearchKeywordParametersDesc')"
          :disabled="isLoading"
        />
      </div>

      <div>
        <Field
          uicontrol="text"
          name="searchCategoryParametersGlobal"
          var-type="array"
          v-model="searchCategoryParametersGlobal"
          :title="translate('SitesManager_SearchCategoryLabel')"
          :inline-help="searchCategoryParamsInlineHelp"
          :disabled="isLoading"
        />
      </div>

      <div>
        <Field
          uicontrol="select"
          name="defaultTimezone"
          :options="timezoneOptions"
          :title="translate('SitesManager_SelectDefaultTimezone')"
          :introduction="translate('SitesManager_DefaultTimezoneForNewWebsites')"
          :inline-help="'#timezoneHelp'"
          :disabled="isLoading"
          v-model="defaultTimezone"
        />
      </div>

      <div>
        <Field
          uicontrol="select"
          name="defaultCurrency"
          v-model="defaultCurrency"
          :options="currencies"
          :title="translate('SitesManager_SelectDefaultCurrency')"
          :introduction="translate('SitesManager_DefaultCurrencyForNewWebsites')"
          :inline-help="translate('SitesManager_CurrencySymbolWillBeUsedForGoals')"
          :disabled="isLoading"
        />
      </div>

      <SaveButton :saving="isSaving" @confirm="saveGlobalSettings()"/>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType, watch } from 'vue';
import {
  Matomo,
  ContentBlock,
  translate,
  format,
  AjaxHelper,
} from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';
import TimezoneStore from '../TimezoneStore/TimezoneStore';
import CurrencyStore from '../CurrencyStore/CurrencyStore';
import GlobalSettingsStore from '../GlobalSettingsStore/GlobalSettingsStore';
import ExcludeQueryParameterSettings from './ExcludeQueryParameterSettings.vue';

interface GlobalSettingsState {
  currentIpAddress: null|string;
  utcTime: Date;
  keepURLFragmentsGlobal: boolean;
  defaultCurrency: string;
  defaultTimezone: string;
  excludedIpsGlobal: string[];
  excludedQueryParametersGlobal: string[];
  excludedUserAgentsGlobal: string[];
  excludedReferrersGlobal: string[];
  searchKeywordParametersGlobal: string[];
  searchCategoryParametersGlobal: string[];
  isSaving: boolean;
  exclusionTypeForQueryParams: string;
}

interface IpFromHeaderResponse {
  value: string;
}

export default defineComponent({
  components: {
    ExcludeQueryParameterSettings,
    ContentBlock,
    Field,
    SaveButton,
  },
  props: {
    commonSensitiveQueryParams: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  data(): GlobalSettingsState {
    const currentDate = new Date();
    const utcTime = new Date(
      currentDate.getUTCFullYear(),
      currentDate.getUTCMonth(),
      currentDate.getUTCDate(),
      currentDate.getUTCHours(),
      currentDate.getUTCMinutes(),
      currentDate.getUTCSeconds(),
    );

    const settings = GlobalSettingsStore.globalSettings.value;

    return {
      currentIpAddress: null,
      utcTime,
      keepURLFragmentsGlobal: settings.keepURLFragmentsGlobal,
      defaultTimezone: settings.defaultTimezone,
      defaultCurrency: settings.defaultCurrency,
      excludedIpsGlobal: (settings.excludedIpsGlobal || '').split(','),
      excludedQueryParametersGlobal:
        (settings.excludedQueryParametersGlobal || '').split(','),
      excludedUserAgentsGlobal: (settings.excludedUserAgentsGlobal || '').split(','),
      excludedReferrersGlobal: (settings.excludedReferrersGlobal || '').split(','),
      searchKeywordParametersGlobal:
        (settings.searchKeywordParametersGlobal || '').split(','),
      searchCategoryParametersGlobal:
        (settings.searchCategoryParametersGlobal || '').split(','),
      isSaving: false,
      exclusionTypeForQueryParams: settings.exclusionTypeForQueryParams,
    };
  },
  created() {
    CurrencyStore.init();
    TimezoneStore.init();
    GlobalSettingsStore.init();

    watch(() => GlobalSettingsStore.globalSettings.value, (settings) => {
      this.keepURLFragmentsGlobal = settings.keepURLFragmentsGlobal;
      this.defaultTimezone = settings.defaultTimezone;
      this.defaultCurrency = settings.defaultCurrency;
      this.excludedIpsGlobal = (settings.excludedIpsGlobal || '').split(',');
      this.excludedQueryParametersGlobal = (settings.excludedQueryParametersGlobal || '')
        .split(',');
      this.excludedUserAgentsGlobal = (settings.excludedUserAgentsGlobal || '').split(',');
      this.excludedReferrersGlobal = (settings.excludedReferrersGlobal || '').split(',');
      this.searchKeywordParametersGlobal = (settings.searchKeywordParametersGlobal || '')
        .split(',');
      this.searchCategoryParametersGlobal = (settings.searchCategoryParametersGlobal || '')
        .split(',');
      this.exclusionTypeForQueryParams = settings.exclusionTypeForQueryParams;
    });

    AjaxHelper.fetch<IpFromHeaderResponse>({ method: 'API.getIpFromHeader' }).then((response) => {
      this.currentIpAddress = response.value;
    });
  },
  methods: {
    saveGlobalSettings() {
      this.isSaving = true;
      GlobalSettingsStore.saveGlobalSettings({
        keepURLFragments: this.keepURLFragmentsGlobal,
        currency: this.defaultCurrency,
        timezone: this.defaultTimezone,
        excludedIps: this.excludedIpsGlobal.join(','),
        excludedQueryParameters: this.excludedQueryParametersGlobal.join(','),
        excludedUserAgents: this.excludedUserAgentsGlobal.join(','),
        excludedReferrers: this.excludedReferrersGlobal.join(','),
        searchKeywordParameters: this.searchKeywordParametersGlobal.join(','),
        searchCategoryParameters: this.searchCategoryParametersGlobal.join(','),
        exclusionTypeForQueryParams: this.exclusionTypeForQueryParams,
      }).then(() => {
        Matomo.helper.redirect({ showaddsite: false });
      }).finally(() => {
        this.isSaving = false;
      });
    },
  },
  computed: {
    isLoading() {
      return GlobalSettingsStore.isLoading.value
        || TimezoneStore.isLoading.value
        || CurrencyStore.isLoading.value;
    },
    timezones() {
      return TimezoneStore.timezones.value;
    },
    timezoneOptions() {
      return this.timezones.map(({ group, label, code }) => ({ group, key: label, value: code }));
    },
    currencies() {
      return CurrencyStore.currencies.value;
    },
    hasSuperUserAccess() {
      return Matomo.hasSuperUserAccess;
    },
    yourCurrentIpAddressIs() {
      return translate('SitesManager_YourCurrentIpAddressIs', `<i>${this.currentIpAddress}</i>`);
    },
    timezoneSupportEnabled() {
      return TimezoneStore.timezoneSupportEnabled.value;
    },
    utcTimeDate() {
      const { utcTime } = this;

      const formatTimePart = (n: number) => n.toString().padStart(2, '0');

      const hours = formatTimePart(utcTime.getHours());
      const minutes = formatTimePart(utcTime.getMinutes());
      const seconds = formatTimePart(utcTime.getSeconds());

      return `${format(this.utcTime)} ${hours}:${minutes}:${seconds}`;
    },
    keepUrlFragmentHelp() {
      return translate(
        'SitesManager_KeepURLFragmentsHelp',
        '<em>#</em>',
        '<em>example.org/index.html#first_section</em>',
        '<em>example.org/index.html</em>',
      );
    },
    searchCategoryParamsInlineHelp() {
      const parts = [
        translate('Goals_Optional'),
        translate('SitesManager_SearchCategoryDesc'),
        translate('SitesManager_SearchCategoryParametersDesc'),
      ];
      return parts.join(' ');
    },
  },
});
</script>

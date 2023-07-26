<template>
  <div class="trackingCodeAdvancedOptions">
    <div class="advance-option">
    <span>
      <a href="javascript:;"
         v-if="!showAdvanced"
         @click.prevent="showAdvanced = true">
        {{ translate('CoreAdminHome_ShowAdvancedOptions') }}
        <span class="icon-chevron-down"></span>
      </a>
      <a href="javascript:;"
         v-if="showAdvanced"
         @click.prevent="showAdvanced = false">
        {{ translate('CoreAdminHome_HideAdvancedOptions') }}
        <span class="icon-chevron-up"></span>
      </a>
    </span>
    </div>

    <div id="javascript-advanced-options" v-show="showAdvanced">
      <p v-html="$sanitize(trackingDocumentationHelp)"></p>
      <div id="optional-js-tracking-options">
        <!-- track across all subdomains -->
        <div id="jsTrackAllSubdomainsInlineHelp" class="inline-help-node">
          <span v-html="$sanitize(mergeSubdomainsDesc)"></span>
          <span v-html="$sanitize(learnMoreText)"></span>
        </div>

        <Field
          uicontrol="checkbox"
          name="javascript-tracking-all-subdomains"
          :model-value="trackAllSubdomains"
          @update:model-value="trackAllSubdomains = $event; updateTrackingCode()"
          :disabled="isLoading"
          :title="`${translate(
            'CoreAdminHome_JSTracking_MergeSubdomains',
          )} ${currentSiteName}`"
          inline-help="#jsTrackAllSubdomainsInlineHelp"
        />
      </div>

      <!-- group page titles by site domain -->
      <div id="jsTrackGroupByDomainInlineHelp" class="inline-help-node">
        {{ translate('CoreAdminHome_JSTracking_GroupPageTitlesByDomainDesc1', currentSiteHost) }}
      </div>

      <Field
        uicontrol="checkbox"
        name="javascript-tracking-group-by-domain"
        :model-value="groupByDomain"
        @update:model-value="groupByDomain = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="translate('CoreAdminHome_JSTracking_GroupPageTitlesByDomain')"
        inline-help="#jsTrackGroupByDomainInlineHelp"
      />

      <!-- track across all site aliases -->
      <div id="jsTrackAllAliasesInlineHelp" class="inline-help-node">
        {{ translate('CoreAdminHome_JSTracking_MergeAliasesDesc', currentSiteAlias) }}
      </div>

      <Field
        uicontrol="checkbox"
        name="javascript-tracking-all-aliases"
        :model-value="trackAllAliases"
        @update:model-value="trackAllAliases = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="`${translate('CoreAdminHome_JSTracking_MergeAliases')} ${currentSiteName}`"
        inline-help="#jsTrackAllAliasesInlineHelp"
      />

      <Field
        uicontrol="checkbox"
        name="javascript-tracking-noscript"
        :model-value="trackNoScript"
        @update:model-value="trackNoScript = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="translate('CoreAdminHome_JSTracking_TrackNoScript')"
      />

      <!-- visitor custom variable -->
      <Field
        uicontrol="checkbox"
        name="javascript-tracking-visitor-cv-check"
        :model-value="trackCustomVars"
        @update:model-value="trackCustomVars = $event; updateTrackingCode()"
        :disabled="isLoading"
        v-show="maxCustomVariables > 0"
        :title="translate('CoreAdminHome_JSTracking_VisitorCustomVars')"
        :inline-help="translate('CoreAdminHome_JSTracking_VisitorCustomVarsDesc')"
      />

      <div id="javascript-tracking-visitor-cv" v-show="trackCustomVars">
        <div class="row">
          <div class="col s12 m3">
            {{ translate('General_Name') }}
          </div>
          <div class="col s12 m3">
            {{ translate('General_Value') }}
          </div>
        </div>
        <div class="row" v-for="(customVar, index) in customVars" :key="index">
          <div class="col s12 m6 l3">
            <input type="text" class="custom-variable-name"
                   @keydown="onCustomVarNameKeydown($event, index)"
                   placeholder="e.g. Type"/>
          </div>
          <div class="col s12 m6 l3">
            <input type="text" class="custom-variable-value"
                   @keydown="onCustomVarValueKeydown($event, index)"
                   placeholder="e.g. Customer"/>
          </div>
        </div>
        <div class="row" v-show="canAddMoreCustomVariables">
          <div class="col s12">
            <a href="javascript:;"
               @click="addCustomVar()"
               class="add-custom-variable"
            >
              <span class="icon-add"></span> {{ translate('General_Add') }}
            </a>
          </div>
        </div>
      </div>

      <!-- cross domain support -->
      <div id="jsCrossDomain" class="inline-help-node">
        {{ translate('CoreAdminHome_JSTracking_CrossDomain') }}
        <br/>
        {{ translate('CoreAdminHome_JSTracking_CrossDomain_NeedsMultipleDomains') }}
      </div>

      <Field
        uicontrol="checkbox"
        name="javascript-tracking-cross-domain"
        :model-value="crossDomain"
        @update:model-value="crossDomain = $event;
      updateTrackingCode(); onCrossDomainToggle();"
        :disabled="isLoading || !hasManySiteUrls"
        :title="translate('CoreAdminHome_JSTracking_EnableCrossDomainLinking')"
        inline-help="#jsCrossDomain"
      />

      <!-- do not track support -->
      <div id="jsDoNotTrackInlineHelp" class="inline-help-node">
        {{ translate('CoreAdminHome_JSTracking_EnableDoNotTrackDesc') }}
        <span v-if="serverSideDoNotTrackEnabled">
        <br/>
        {{ translate('CoreAdminHome_JSTracking_EnableDoNotTrack_AlreadyEnabled') }}
        </span>
      </div>

      <Field
        uicontrol="checkbox"
        name="javascript-tracking-do-not-track"
        :model-value="doNotTrack"
        @update:model-value="doNotTrack = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="translate('CoreAdminHome_JSTracking_EnableDoNotTrack')"
        inline-help="#jsDoNotTrackInlineHelp"
      />

      <!-- disable all cookies options -->
      <Field
        uicontrol="checkbox"
        name="javascript-tracking-disable-cookies"
        :model-value="disableCookies"
        @update:model-value="disableCookies = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="translate('CoreAdminHome_JSTracking_DisableCookies')"
        :inline-help="translate('CoreAdminHome_JSTracking_DisableCookiesDesc')"
      />

      <!-- custom campaign name/keyword query params -->
      <div id="jsTrackCampaignParamsInlineHelp"
           class="inline-help-node"
           v-html="$sanitize(jsTrackCampaignParamsInlineHelp)">
      </div>

      <Field
        uicontrol="checkbox"
        name="custom-campaign-query-params-check"
        :model-value="useCustomCampaignParams"
        @update:model-value="useCustomCampaignParams = $event; updateTrackingCode()"
        :disabled="isLoading"
        :title="translate('CoreAdminHome_JSTracking_CustomCampaignQueryParam')"
        inline-help="#jsTrackCampaignParamsInlineHelp"
      />

      <div v-show="useCustomCampaignParams" id="js-campaign-query-param-extra">
        <div class="row">
          <div class="col s12">
            <Field
              uicontrol="text"
              name="custom-campaign-name-query-param"
              :model-value="customCampaignName"
              @update:model-value="customCampaignName = $event; updateTrackingCode()"
              :disabled="isLoading"
              :title="translate('CoreAdminHome_JSTracking_CampaignNameParam')"
            />
          </div>
        </div>
        <div class="row">
          <div class="col s12">
            <Field
              uicontrol="text"
              name="custom-campaign-keyword-query-param"
              :model-value="customCampaignKeyword"
              @update:model-value="customCampaignKeyword = $event; updateTrackingCode()"
              :disabled="isLoading"
              :title="translate('CoreAdminHome_JSTracking_CampaignKwdParam')"
            />
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  SiteRef,
  debounce,
  Matomo,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface CustomVar {
  name: string;
  value: string;
}

interface JsTrackingCodeAdvancedOptionsState {
  showAdvanced: boolean;
  trackAllSubdomains: boolean;
  isLoading: boolean;
  siteUrls: Record<string, string[]>;
  siteExcludedQueryParams: Record<string, string[]>,
  siteExcludedReferrers: Record<string, string[]>,
  crossDomain: boolean;
  groupByDomain: boolean;
  trackAllAliases: boolean;
  trackNoScript: boolean;
  trackCustomVars: boolean;
  customVars: CustomVar[];
  canAddMoreCustomVariables: boolean;
  doNotTrack: boolean;
  disableCookies: boolean;
  useCustomCampaignParams: boolean;
  customCampaignName: string;
  customCampaignKeyword: string;
  trackingCodeAbortController: AbortController | null;
}

interface GetJavascriptTagResponse {
  value: string;
}

function getHostNameFromUrl(url: string) {
  const urlObj = new URL(url);
  return urlObj.hostname;
}

function getCustomVarArray(cvars: CustomVar[]) {
  return cvars.filter((cv) => !!cv.name).map((cv) => [cv.name, cv.value]);
}

const piwikHost = window.location.host;
const piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));

export default defineComponent({
  props: {
    site: {
      type: Object,
      required: true,
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean,
  },
  data(): JsTrackingCodeAdvancedOptionsState {
    return {
      showAdvanced: false,
      trackAllSubdomains: false,
      isLoading: false,
      siteUrls: {},
      siteExcludedQueryParams: {},
      siteExcludedReferrers: {},
      crossDomain: false,
      groupByDomain: false,
      trackAllAliases: false,
      trackNoScript: false,
      trackCustomVars: false,
      customVars: [],
      canAddMoreCustomVariables: !!this.maxCustomVariables && this.maxCustomVariables > 0,
      doNotTrack: false,
      disableCookies: false,
      useCustomCampaignParams: false,
      customCampaignName: '',
      customCampaignKeyword: '',
      trackingCodeAbortController: null,
    };
  },
  emits: ['updateTrackingCode'],
  components: {
    Field,
  },
  created() {
    if (this.site && this.site.id) {
      this.onSiteChanged(this.site as SiteRef);
    }

    this.onCustomVarNameKeydown = debounce(this.onCustomVarNameKeydown, 100);
    this.onCustomVarValueKeydown = debounce(this.onCustomVarValueKeydown, 100);

    this.addCustomVar();
  },
  watch: {
    site(newValue: SiteRef) {
      this.onSiteChanged(newValue);
    },
  },
  methods: {
    onSiteChanged(newValue: SiteRef) {
      const idSite = newValue.id;

      const promises: Promise<unknown>[] = [];
      if (!this.siteUrls[idSite]) {
        this.isLoading = true;
        promises.push(
          AjaxHelper.fetch({
            module: 'API',
            method: 'SitesManager.getSiteUrlsFromId',
            idSite,
            filter_limit: '-1',
          }).then((data) => {
            this.siteUrls[idSite] = data || [];
          }),
        );
      }

      if (!this.siteExcludedQueryParams[idSite]) {
        this.isLoading = true;

        promises.push(
          AjaxHelper.fetch({
            module: 'API',
            method: 'Overlay.getExcludedQueryParameters',
            idSite,
            filter_limit: '-1',
          }).then((data) => {
            this.siteExcludedQueryParams[idSite] = data || [];
          }),
        );
      }

      if (!this.siteExcludedReferrers[idSite]) {
        this.isLoading = true;

        promises.push(
          AjaxHelper.fetch({
            module: 'API',
            method: 'SitesManager.getExcludedReferrers',
            idSite,
            filter_limit: '-1',
          }).then((data) => {
            this.siteExcludedReferrers[idSite] = [];
            Object.values(data || []).forEach((referrer: unknown) => {
              this.siteExcludedReferrers[idSite].push((referrer as string).replace(/^https?:\/\//, ''));
            });
          }),
        );
      }

      Promise.all(promises).then(() => {
        // eslint-disable-next-line
        const refs = (this.$refs.jsTrackingCodeAdvanceOption as any);
        this.isLoading = false;
        this.updateCurrentSiteInfo();
        this.updateTrackingCode();
      });
    },
    updateCurrentSiteInfo() {
      if (!this.hasManySiteUrls) {
        // we make sure to disable cross domain if it has only one url or less
        this.crossDomain = false;
      }
    },
    onCrossDomainToggle() {
      if (this.crossDomain) {
        this.trackAllAliases = true;
      }
    },
    updateTrackingCode() {
      // get params used to generate JS code
      const params: Record<string, unknown> = {
        piwikUrl: `${piwikHost}${piwikPath}`,
        groupPageTitlesByDomain: this.groupByDomain ? 1 : 0,
        mergeSubdomains: this.trackAllSubdomains ? 1 : 0,
        mergeAliasUrls: this.trackAllAliases ? 1 : 0,
        visitorCustomVariables: this.trackCustomVars ? getCustomVarArray(this.customVars) : 0,
        customCampaignNameQueryParam: null,
        customCampaignKeywordParam: null,
        doNotTrack: this.doNotTrack ? 1 : 0,
        disableCookies: this.disableCookies ? 1 : 0,
        crossDomain: this.crossDomain ? 1 : 0,
        trackNoScript: this.trackNoScript ? 1 : 0,
        forceMatomoEndpoint: 1,
      };

      if (this.siteExcludedQueryParams[this.site.id]) {
        params.excludedQueryParams = this.siteExcludedQueryParams[this.site.id];
      }

      if (this.siteExcludedReferrers[this.site.id]) {
        params.excludedReferrers = this.siteExcludedReferrers[this.site.id];
      }

      if (this.useCustomCampaignParams) {
        params.customCampaignNameQueryParam = this.customCampaignName;
        params.customCampaignKeywordParam = this.customCampaignKeyword;
      }

      if (this.trackingCodeAbortController) {
        this.trackingCodeAbortController.abort();
        this.trackingCodeAbortController = null;
      }

      this.trackingCodeAbortController = new AbortController();

      AjaxHelper.post<GetJavascriptTagResponse>(
        {
          module: 'API',
          format: 'json',
          method: 'SitesManager.getJavascriptTag',
          idSite: this.site.id,
        },
        params,
        {
          abortController: this.trackingCodeAbortController,
        },
      ).then((response) => {
        this.trackingCodeAbortController = null;
        this.$emit('updateTrackingCode', response.value);
      });
    },
    addCustomVar() {
      if (this.canAddMoreCustomVariables) {
        this.customVars.push({ name: '', value: '' });
      }

      this.canAddMoreCustomVariables = !!this.maxCustomVariables
        && this.maxCustomVariables > this.customVars.length;
    },
    onCustomVarNameKeydown(event: KeyboardEvent, index: number) {
      setTimeout(() => {
        this.customVars[index].name = (event.target as HTMLInputElement).value;
        this.updateTrackingCode();
      });
    },
    onCustomVarValueKeydown(event: KeyboardEvent, index: number) {
      setTimeout(() => {
        this.customVars[index].value = (event.target as HTMLInputElement).value;
        this.updateTrackingCode();
      });
    },
  },
  computed: {
    hasManySiteUrls() {
      const { site } = this;
      return this.siteUrls[site.id] && this.siteUrls[site.id].length > 1;
    },
    currentSiteHost() {
      const siteUrl = this.siteUrls[this.site.id]?.[0];
      if (!siteUrl) {
        return '';
      }

      return getHostNameFromUrl(siteUrl);
    },
    currentSiteAlias() {
      const defaultAliasUrl = `x.${this.currentSiteHost}`;
      const alias = this.siteUrls[this.site.id]?.[1];
      return alias || defaultAliasUrl;
    },
    currentSiteName() {
      return Matomo.helper.htmlEntities(this.site.name);
    },
    mergeSubdomainsDesc() {
      return translate(
        'CoreAdminHome_JSTracking_MergeSubdomainsDesc',
        `x.${this.currentSiteHost}`,
        `y.${this.currentSiteHost}`,
      );
    },
    learnMoreText() {
      const subdomainsLink = 'https://developer.matomo.org/guides/tracking-javascript-guide'
        + '#measuring-domains-andor-sub-domains';
      return translate(
        'General_LearnMore',
        ` (<a href="${subdomainsLink}" rel="noreferrer noopener" target="_blank">`,
        '</a>)',
      );
    },
    jsTrackCampaignParamsInlineHelp() {
      return translate(
        'CoreAdminHome_JSTracking_CustomCampaignQueryParamDesc',
        '<a href="https://matomo.org/faq/general/faq_119" rel="noreferrer noopener" target="_blank">',
        '</a>',
      );
    },
    trackingDocumentationHelp() {
      return translate(
        'CoreAdminHome_JSTrackingDocumentationHelp',
        '<a rel="noreferrer noopener" target="_blank" href="https://developer.matomo.org/guides/tracking-javascript-guide">',
        '</a>',
      );
    },
  },
});

</script>

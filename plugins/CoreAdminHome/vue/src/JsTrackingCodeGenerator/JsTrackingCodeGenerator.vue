<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    anchor="javaScriptTracking"
    :content-title="translate('CoreAdminHome_JavaScriptTracking')"
  >
    <div id="js-code-options">
      <p>
        {{ translate('CoreAdminHome_JSTrackingIntro1') }}
        <br/><br/>
        {{ translate('CoreAdminHome_JSTrackingIntro2') }}
        <span v-html="jsTrackingIntro3a"></span>
        <span v-html="' ' + jsTrackingIntro3b"></span>
        <br/><br/>
        <span v-html="jsTrackingIntro4a"></span>
        <br/><br/>
        <span v-html="jsTrackingIntro5"></span>
        <br><br/>
        {{ translate('SitesManager_InstallationGuides') }} :
        <a href="https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/"
           target="_blank" rel="noopener">WordPress</a> |
        <a href="https://matomo.org/faq/new-to-piwik/how-do-i-integrate-matomo-with-squarespace-website/"
           target="_blank" rel="noopener">Squarespace</a> |
        <a href="https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-wix/"
           target="_blank" rel="noopener">Wix</a> |
        <a href="https://matomo.org/faq/how-to-install/faq_19424/"
           target="_blank" rel="noopener">SharePoint</a> |
        <a href="https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-joomla/"
           target="_blank" rel="noopener">Joomla</a> |
        <a href="https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-shopify-store/"
           target="_blank" rel="noopener">Shopify</a> |
        <a href="https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-within-gtm-google-tag-manager/"
           target="_blank" rel="noopener">Google Tag Manager</a>
      </p>

      <Field
        uicontrol="site"
        name="js-tracker-website"
        class="jsTrackingCodeWebsite"
        v-model="site"
        :introduction="translate('General_Website')"
      />

      <div id="javascript-output-section">
        <div class="valign-wrapper trackingHelpHeader matchWidth">
          <div>
            <h3>{{ translate('General_JsTrackingTag') }}</h3>

            <p>
              {{ translate('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead', "&lt;/head&gt;") }}
            </p>
          </div>

          <button class="btn" id="emailJsBtn" @click="sendEmail()">
            {{ translate('SitesManager_EmailInstructionsButton') }}
          </button>
        </div>
        <div id="javascript-text">
          <pre v-select-on-focus="{}" class="codeblock" v-text="trackingCode" ref="trackingCode"/>
        </div>
      </div>

      <div id="optional-js-tracking-options">
        <!-- track across all subdomains -->
        <div id="jsTrackAllSubdomainsInlineHelp" class="inline-help-node">
          <span v-html="mergeSubdomainsDesc"></span>
          <span v-html="learnMoreText"></span>
        </div>

        <Field
          uicontrol="checkbox"
          name="javascript-tracking-all-subdomains"
          :model-value="trackAllSubdomains"
          @update:model-value="trackAllSubdomains = $event; updateTrackingCode()"
          :disabled="isLoading"
          :introduction="translate('General_Options')"
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
    </div>

    <Field
      uicontrol="checkbox"
      name="javascript-tracking-noscript"
      :model-value="trackNoScript"
      @update:model-value="trackNoScript = $event; updateTrackingCode()"
      :disabled="isLoading"
      :title="translate('CoreAdminHome_JSTracking_TrackNoScript')"
    />

    <h3>{{ translate('Mobile_Advanced') }}</h3>

    <p>
      <a href="javascript:;"
         v-show="!showAdvanced"
         @click.prevent="showAdvanced = true">{{ translate('General_Show') }}</a>
      <a href="javascript:;"
         v-show="showAdvanced"
         @click.prevent="showAdvanced = false">{{ translate('General_Hide') }}</a>
    </p>

    <div id="javascript-advanced-options" v-show="showAdvanced">

      <!-- visitor custom variable -->
      <Field
        uicontrol="checkbox"
        name="javascript-tracking-visitor-cv-check"
        :model-value="trackCustomVars"
        @update:model-value="trackCustomVars = $event; updateTrackingCode()"
        :disabled="isLoading"
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
        @update:model-value="crossDomain = $event; updateTrackingCode(); onCrossDomainToggle();"
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
           v-html="jsTrackCampaignParamsInlineHelp">
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

  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  translate,
  AjaxHelper,
  SiteRef,
  SelectOnFocus,
  debounce,
  Matomo,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface CustomVar {
  name: string;
  value: string;
}

interface JsTrackingCodeGeneratorState {
  showAdvanced: boolean;
  site: SiteRef;
  trackingCode: string;
  trackAllSubdomains: boolean;
  isLoading: boolean;
  siteUrls: Record<string, string[]>;
  siteExcludedQueryParams: Record<string, string[]>,
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
  trackingCodeAbortController: AbortController|null;
  isHighlighting: boolean;
}

interface GetJavascriptTagResponse {
  value: string;
}

function getHostNameFromUrl(url: string) {
  const urlObj = new URL(url);
  return urlObj.hostname;
}

function getCustomVarArray(cvars: CustomVar[]) {
  return cvars.map((cv) => [cv.name, cv.value]);
}

const { $ } = window;

const piwikHost = window.location.host;
const piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));

export default defineComponent({
  props: {
    defaultSite: {
      type: Object,
      required: true,
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean,
  },
  data(): JsTrackingCodeGeneratorState {
    return {
      showAdvanced: false,
      site: this.defaultSite as SiteRef,
      trackingCode: '',
      trackAllSubdomains: false,
      isLoading: false,
      siteUrls: {},
      siteExcludedQueryParams: {},
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
      isHighlighting: false,
    };
  },
  components: {
    ContentBlock,
    Field,
  },
  directives: {
    SelectOnFocus,
  },
  created() {
    this.onCustomVarNameKeydown = debounce(this.onCustomVarNameKeydown, 100);
    this.onCustomVarValueKeydown = debounce(this.onCustomVarValueKeydown, 100);

    this.addCustomVar();

    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
  },
  watch: {
    site(newValue: SiteRef) {
      this.onSiteChanged(newValue);
    },
  },
  methods: {
    onSiteChanged(newValue: SiteRef) {
      const idSite = newValue.id;

      // if data is already loaded, don't do an AJAX request

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

      Promise.all(promises).then(() => {
        this.isLoading = false;
        this.updateCurrentSiteInfo();
        this.updateTrackingCode();
      });
    },
    sendEmail() {
      let subjectLine = translate('SitesManager_EmailInstructionsSubject');
      subjectLine = encodeURIComponent(subjectLine);

      let { trackingCode } = this;
      trackingCode = trackingCode.replace(/<[^>]+>/g, '');

      let bodyText = `${translate('SitesManager_JsTrackingTagHelp')}. ${translate(
        'CoreAdminHome_JSTracking_CodeNoteBeforeClosingHeadEmail',
        '\'head',
      )}\n${trackingCode}`;
      bodyText = encodeURIComponent(bodyText);

      const linkText = `mailto:?subject=${subjectLine}&body=${bodyText}`;
      window.location.href = linkText;
    },
    onCrossDomainToggle() {
      if (this.crossDomain) {
        this.trackAllAliases = true;
      }
    },
    updateTrackingCode() {
      const { site } = this;

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

      if (this.siteExcludedQueryParams[site.id]) {
        params.excludedQueryParams = this.siteExcludedQueryParams[site.id];
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
          idSite: site.id,
        },
        params,
        {
          abortController: this.trackingCodeAbortController,
        },
      ).then((response) => {
        this.trackingCodeAbortController = null;

        this.trackingCode = response.value;

        const jsCodeTextarea = $(this.$refs.trackingCode as HTMLElement);
        if (jsCodeTextarea && !this.isHighlighting) {
          this.isHighlighting = true;
          jsCodeTextarea.effect('highlight', {
            complete: () => {
              this.isHighlighting = false;
            },
          }, 1500);
        }
      });
    },
    updateCurrentSiteInfo() {
      if (!this.hasManySiteUrls) {
        // we make sure to disable cross domain if it has only one url or less
        this.crossDomain = false;
      }
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
    jsTrackingIntro3a() {
      return translate(
        'CoreAdminHome_JSTrackingIntro3a',
        '<a href="https://matomo.org/integrate/" rel="noreferrer noopener" target="_blank">',
        '</a>',
      );
    },
    jsTrackingIntro3b() {
      return translate('CoreAdminHome_JSTrackingIntro3b');
    },
    jsTrackingIntro4a() {
      return translate(
        'CoreAdminHome_JSTrackingIntro4',
        '<a href="#image-tracking-link">',
        '</a>',
      );
    },
    jsTrackingIntro5() {
      return translate(
        'CoreAdminHome_JSTrackingIntro5',
        '<a rel="noreferrer noopener" target="_blank" '
        + 'href="https://developer.matomo.org/guides/tracking-javascript-guide">',
        '</a>',
      );
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
        '<a href="https://matomo.org/faq/general/#faq_119" rel="noreferrer noopener" target="_blank">',
        '</a>',
      );
    },
  },
});

</script>

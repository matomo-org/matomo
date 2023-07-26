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
        <span v-html="$sanitize(jsTrackingIntro3a)"></span>
        <span v-html="$sanitize(' ' + jsTrackingIntro3b)"></span>
        <br/><br/>
        <span v-html="$sanitize(jsTrackingIntro4a)"></span>
        <br/><br/>
        <span v-html="$sanitize(jsTrackingIntro5)"></span>
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
        ref="site"
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
        <div id="javascript-email-button">
          <button class="btn" id="emailJsBtn" @click="sendEmail()">
            {{ translate('SitesManager_EmailInstructionsButton') }}
          </button>
        </div>
        </div>
        <div id="javascript-text">
          <div>
            <pre v-copy-to-clipboard="{}" class="codeblock" v-text="trackingCode"
                 ref="trackingCode"/>
          </div>
        </div>
      </div>
    </div>
    <JsTrackingCodeAdvancedOptions
      :site="site"
      :max-custom-variables="maxCustomVariables"
      :server-side-do-not-track-enabled="serverSideDoNotTrackEnabled"
      @updateTrackingCode="updateTrackingCode"
      ref="jsTrackingCodeAdvanceOption"/>

  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  translate,
  AjaxHelper,
  SiteRef,
  CopyToClipboard,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import JsTrackingCodeAdvancedOptions from './JsTrackingCodeAdvancedOptions.vue';

interface JsTrackingCodeGeneratorState {
  site: SiteRef;
  trackingCode: string;
  isHighlighting: boolean;
  consentManagerName: string;
  consentManagerUrl: string;
  consentManagerIsConnected: boolean;
}

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
      site: this.defaultSite as SiteRef,
      trackingCode: '',
      isHighlighting: false,
      consentManagerName: '',
      consentManagerUrl: '',
      consentManagerIsConnected: false,
    };
  },
  components: {
    JsTrackingCodeAdvancedOptions,
    ContentBlock,
    Field,
  },
  directives: {
    CopyToClipboard,
  },
  created() {
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
    updateTrackingCode(code:string) {
      this.trackingCode = code;

      const jsCodeTextarea = $(this.$refs.trackingCode as HTMLElement);
      if (jsCodeTextarea && !this.isHighlighting) {
        this.isHighlighting = true;
        jsCodeTextarea.effect('highlight', {
          complete: () => {
            this.isHighlighting = false;
          },
        }, 1500);
      }
    },
    onSiteChanged(newValue: SiteRef) {
      const idSite = newValue.id;

      AjaxHelper.fetch(
        {
          module: 'API',
          format: 'json',
          method: 'Tour.detectConsentManager',
          idSite,
          filter_limit: '-1',
        },
      ).then((response) => {
        if (Object.prototype.hasOwnProperty.call(response, 'name')) {
          this.consentManagerName = response.name;
        }
        if (Object.prototype.hasOwnProperty.call(response, 'url')) {
          this.consentManagerUrl = response.url;
        }
        this.consentManagerIsConnected = response.isConnected;
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

      if (this.consentManagerName !== '' && this.consentManagerUrl !== '') {
        bodyText += translate('CoreAdminHome_JSTracking_ConsentManagerDetected', this.consentManagerName,
          this.consentManagerUrl);
        if (this.consentManagerIsConnected) {
          bodyText += `\n${translate('CoreAdminHome_JSTracking_ConsentManagerConnected', this.consentManagerName)}`;
        }
      }
      bodyText = encodeURIComponent(bodyText);

      const linkText = `mailto:?subject=${subjectLine}&body=${bodyText}`;
      window.location.href = linkText;
    },
  },
  computed: {
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
  },
});

</script>

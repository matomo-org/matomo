<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="widgetize">
    <div v-content-intro>
      <h2>
        <EnrichedHeadline>{{ title }}</EnrichedHeadline>
      </h2>
      <p v-html="$sanitize(intro)"></p>
    </div>
    <ContentBlock content-title="Authentication">
      <p v-html="$sanitize(viewableAnonymously)"></p>
    </ContentBlock>
    <ContentBlock content-title="Widgetize dashboards">
      <div>
        <p>
          <span v-html="$sanitize(displayInIframe)"></span>
          <br/>
        </p>
        <pre
          v-text="dashboardCode"
          v-select-on-focus="{}"
        ></pre>
        <p>
          <br />
          <span v-html="$sanitize(displayInIframeAllSites)"></span>
          <br />
        </p>
        <pre
          v-text="allWebsitesDashboardCode"
          v-select-on-focus="{}"
        ></pre>
      </div>
    </ContentBlock>
    <ContentBlock :content-title="translate('Widgetize_Reports')">
      <div>
        <p>{{ translate('Widgetize_SelectAReport') }}</p>
        <div>
          <WidgetPreview />
        </div>
        <br class="clearfix" />
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  ContentIntro,
  EnrichedHeadline,
  ContentBlock,
  SelectOnFocus,
  MatomoUrl,
} from 'CoreHome';
import WidgetPreview from '../WidgetPreview/WidgetPreview.vue';

interface ExportWidgetState {
  dashboardUrl: string;
  allWebsitesDashboardUrl: string;
}

function getIframeCode(iframeUrl: string) {
  const url = iframeUrl.replace(/"/g, '&quot;');
  return `<iframe src="${url}" frameborder="0" marginheight="0" marginwidth="0" width="100%" `
    + 'height="100%"></iframe>';
}

export default defineComponent({
  props: {
    title: {
      type: String,
      required: true,
    },
  },
  components: {
    EnrichedHeadline,
    ContentBlock,
    WidgetPreview,
  },
  directives: {
    ContentIntro,
    SelectOnFocus,
  },
  data(): ExportWidgetState {
    const port = window.location.port === '' ? '' : `:${window.location.port}`;
    const path = window.location.pathname;
    const urlPath = `${window.location.protocol}//${window.location.hostname}${port}${path}`;

    return {
      dashboardUrl: `${urlPath}?${MatomoUrl.stringify({
        module: 'Widgetize',
        action: 'iframe',
        moduleToWidgetize: 'Dashboard',
        actionToWidgetize: 'index',
        idSite: Matomo.idSite,
        period: 'week',
        date: 'yesterday',
      })}`,
      allWebsitesDashboardUrl: `${urlPath}?${MatomoUrl.stringify({
        module: 'Widgetize',
        action: 'iframe',
        moduleToWidgetize: 'MultiSites',
        actionToWidgetize: 'standalone',
        idSite: Matomo.idSite,
        period: 'week',
        date: 'yesterday',
      })}`,
    };
  },
  computed: {
    dashboardCode() {
      return getIframeCode(this.dashboardUrl);
    },
    allWebsitesDashboardCode() {
      return getIframeCode(this.allWebsitesDashboardUrl);
    },
    intro() {
      return translate(
        'Widgetize_Intro',
        `<a
          rel="noreferrer noopener"
          target="_blank"
          href="https://matomo.org/docs/embed-piwik-report/"
        >`,
        '</a>',
      );
    },
    viewableAnonymously() {
      return translate(
        'Widgetize_ViewableAnonymously',
        `<a
          href="index.php?module=UsersManager"
          rel="noreferrer noopener"
          target="_blank"
        >`,
        '</a>',
        `<a
          rel="noreferrer noopener"
          target="_blank"
          href="${this.linkTo({ module: 'UsersManager', action: 'userSecurity' })}"
        >`,
        '</a>',
      );
    },
    displayInIframe() {
      return translate(
        'Widgetize_DisplayDashboardInIframe',
        `<a
          rel="noreferrer noopener"
          target="_blank"
          href="${this.dashboardUrl}"
        >`,
        '</a>',
      );
    },
    displayInIframeAllSites() {
      return translate(
        'Widgetize_DisplayDashboardInIframeAllSites',
        `<a
          rel="noreferrer noopener"
          target="_blank"
          id="linkAllWebsitesDashboardUrl"
          href="${this.allWebsitesDashboardUrl}"
        >`,
        '</a>',
      );
    },
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
  },
});
</script>

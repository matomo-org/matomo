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
      <p>With Matomo, you can <a
          rel="noreferrer noopener"
          target="_blank"
          href="https://matomo.org/docs/embed-piwik-report/"
        >export your Web Analytics reports</a> on your blog, website, or intranet dashboard...
        in one click.</p>
    </div>
    <ContentBlock content-title="Authentication">
      <p>
        If you want your widgets to be viewable by everybody, you first have to set the 'view'
        permissions to the anonymous user in the <a
          href="index.php?module=UsersManager"
          rel="noreferrer noopener"
          target="_blank"
        >Users Management section</a>.
        <br />Alternatively, if you are publishing widgets on a password protected or private page,
        you don't necessarily have to allow 'anonymous' to view your reports. In this case, you can
        add the secret <code>token_auth</code> parameter in the widget URL.
        You can manage your auth tokens on your <a
          rel="noreferrer noopener"
          target="_blank"
          :href="linkTo({'module': 'UsersManager', 'action': 'userSecurity'})"
        >Security page</a>.
      </p>
    </ContentBlock>
    <ContentBlock content-title="Widgetize dashboards">
      <div>
        <p>
            You can also display the full Matomo dashboard in your application or website in an
          IFRAME (<a
            rel="noreferrer noopener"
            target="_blank"
            :href="dashboardUrl"
          >see example</a>).
          The date parameter can be set to a specific calendar date, "today", or "yesterday". The
          period parameter can be set to "day", "week", "month", or
          "year".
          The language parameter can be set to the language code of a translation, such as
          language=fr. For example, for idSite=1 and date=yesterday, you can write:
          <br />
        </p>
        <pre
          v-text="dashboardCode"
          v-select-on-focus="{}"
        ></pre>
        <p>
          <br />
          You can also widgetize the all websites dashboard in an IFRAME
          (<a
            rel="noreferrer noopener"
            target="_blank"
            id="linkAllWebsitesDashboardUrl"
            :href="allWebsitesDashboardUrl"
          >see example</a>)
          <br />
        </p>
        <pre
          v-text="allWebsitesDashboardCode"
          v-select-on-focus="{}"
        ></pre>
      </div>
    </ContentBlock>
    <ContentBlock content-title="Widgetize reports">
      <div>
        <p>Select a report, and copy paste in your page the embed code below the widget:</p>
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
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        params,
      })}`;
    },
  },
});
</script>

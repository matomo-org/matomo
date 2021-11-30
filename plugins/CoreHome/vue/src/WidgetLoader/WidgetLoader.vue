<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <ActivityIndicator
      :loading-message="loadingMessage"
      :loading="loading"
    />
    <div v-show="loadingFailed">
      <h2 v-if="widgetName">{{ widgetName }}</h2>
      <div class="notification system notification-error">
        {{ translate('General_ErrorRequest', '', '') }}
        <a
          rel="noreferrer noopener"
          target="_blank"
          href="https://matomo.org/faq/troubleshooting/faq_19489/"
          v-if="hasErrorFaqLink"
        >
          {{ translate('General_ErrorRequestFaqLink') }}
        </a>
      </div>
    </div>
    <div class="theWidgetContent" ref="widgetContent" />
  </div>
</template>

<script lang="ts">
import { IRootScopeService } from 'angular';
import { defineComponent } from 'vue';
import ActivityIndicator from '../ActivityIndicator/ActivityIndicator.vue';
import translate from '../translate';
import Matomo from '../Matomo/Matomo';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import { NotificationsStore } from '../Notification';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

/**
 * Loads any custom widget or URL based on the given parameters.
 *
 * The currently active idSite, period, date and segment (if needed) is automatically
 * appended to the parameters. If this widget is removed from the DOM and requests are in
 * progress, these requests will be aborted. A loading message or an error message on failure
 * is shown as well. It's kinda similar to ng-include but there it is not possible to
 * listen to HTTP errors etc.
 *
 * Example:
 * <WidgetLoader :widget-params="{module: '', action: '', ...}"/>
 */
export default defineComponent({
  props: {
    widgetParams: Object,
    widgetName: String,
  },
  components: {
    ActivityIndicator,
  },
  data() {
    return {
      loading: false,
      loadingFailed: '',
      changeCounter: 0, // TODO: check that there is no rerender here?
      lastWidgetRequest: null,
      currentScope: null,
    };
  },
  watch: {
    widgetParams(parameters: Record<string, unknown>) {
      if (parameters) {
        this.loadWidgetUrl(parameters, this.changeCounter += 1);
      }
    },
  },
  computed: {
    loadingMessage() {
      if (!this.widgetName) {
        return translate('General_LoadingData');
      }

      return translate('General_LoadingPopover', this.widgetName);
    },
    hasErrorFaqLink() {
      const isGeneralSettingsAdminEnabled = Matomo.config.enable_general_settings_admin;
      const isPluginsAdminEnabled = Matomo.config.enable_plugins_admin;

      return Matomo.hasSuperUserAccess
        && (isGeneralSettingsAdminEnabled
          || isPluginsAdminEnabled);
    },
  },
  unmounted() {
    this.cleanupLastWidgetContent();
  },
  methods: {
    abortHttpRequestIfNeeded() {
      if (this.lastWidgetRequest) {
        this.lastWidgetRequest.abort();
        this.lastWidgetRequest = null;
      }
    },
    cleanupLastWidgetContent() {
      const { widgetContent } = this.$refs;
      if (widgetContent) {
        widgetContent.innerHTML = '';
      }
      if (this.currentScope) {
        this.currentScope.$destroy();
      }
    },
    getWidgetUrl(parameters: Record<string, unknown>): Record<string, unknown> {
      // TODO: test this
      // happens eg in exported widget etc when URL does not have #?...
      // if (!Object.keys(hashParams).length
      //   || hashParams.idSite
      // ) {
      //   hashParams = { idSite: '', period: '', date: '' };
      // }

      const urlParams: { urlParams: Record<string, unknown> } = { ...MatomoUrl.parsed.value };
      delete urlParams.category;
      delete urlParams.subcategory;

      const credentials: Record<string, unknown> = {};
      if (Matomo.shouldPropagateTokenAuth
        && urlParams.token_auth
      ) {
        if (!Matomo.broadcast.isWidgetizeRequestWithoutSession()) {
          credentials.force_api_session = '1';
        }
        credentials.token_auth = urlParams.token_auth;
      }

      const fullParameters = {
        // defaults
        ...urlParams,
        showtitle: '1',

        // given parameters
        ...parameters,

        // overrides
        ...(urlParams.segment && { segment: urlParams.segment }),
        ...credentials,
        random: Math.floor(Math.random() * 10000),
      };

      return fullParameters;
    },
    loadWidgetUrl(parameters: Record<string, unknown>, thisChangeId: number) {
      this.loading = true;

      this.abortHttpRequestIfNeeded();
      this.cleanupLastWidgetContent();

      this.lastWidgetRequest = AjaxHelper.fetch(this.getWidgetUrl(parameters), {
        format: 'html',
        headers: {
          'X-Requested-With': 'XMLHttpRequest', // TODO: test this
        },
      });

      this.lastWidgetRequest.then((response) => {
        if (thisChangeId !== this.changeCounter || !response || typeof response !== 'string') {
          // another widget was requested meanwhile, ignore this response
          return;
        }

        this.lastWidgetRequest = null;
        this.loading = false;
        this.loadingFailed = false;

        const { widgetContent }: { widgetContent: HTMLElement } = this.$refs;
        window.$(widgetContent).html(response);

        if (this.widgetName) {
          // we need to respect the widget title, which overwrites a possibly set report title
          const $content = window.$(widgetContent);
          let $title = $content.find('> .card-content .card-title');
          if (!$title.length) {
            $title = $content.find('> h2');
          }

          if ($title.length) {
            // required to use htmlEntities since it also escapes '{{' format items
            $title.html(Matomo.helper.htmlEntities(this.widgetName));
          }
        }

        const $rootScope: IRootScopeService = Matomo.helper.getAngularDependency('$rootScope');
        const scope = $rootScope.$new();
        this.currentScope = scope;

        const contentElement = widgetContent.firstElementChild;
        Matomo.helper.compileAngularComponents(contentElement, { scope });

        NotificationsStore.parseNotificationDivs();

        setTimeout(() => {
          Matomo.postEvent('widget:loaded', {
            parameters,
            element: contentElement,
          });
        });
      }).catch((response) => {
        if (thisChangeId !== this.changeCounter) {
          // another widget was requested meanwhile, ignore this response
          return;
        }

        this.lastWidgetRequest = null;
        this.cleanupLastWidgetContent();

        this.loading = false;

        if (response.xhrStatus === 'abort') {
          return;
        }

        this.loadingFailed = true;
      });
    },
  },
});
</script>

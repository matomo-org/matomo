<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="row marketplaceActions" ref="root">
    <div class="col s12 m6 l4">
      <Field
        uicontrol="select"
        name="plugin_type"
        :model-value="pluginTypeFilter"
        @update:model-value="updateType"
        :title="translate('Marketplace_Show')"
        :full-width="true"
        :options="pluginTypeOptions"
      >
      </Field>
    </div>
    <div class="col s12 m6 l4">
      <Field
        uicontrol="select"
        name="plugin_sort"
        :model-value="pluginSort"
        @update:model-value="updateSort"
        :title="translate('Marketplace_Sort')"
        :full-width="true"
        :options="pluginSortOptions"
      >
      </Field>
    </div>
    <!-- Hide filters and search for themes because we don't have many of them -->
    <div class="col s12 m12 l4 " v-if="pluginsToShow?.length > 20 || searchQuery">
      <div class="plugin-search">
        <div>
          <Field
            uicontrol="text"
            name="query"
            :title="queryInputTitle"
            :full-width="true"
            :model-value="searchQuery"
            @update:model-value="updateQuery"
          >
          </Field>
        </div>
        <span
          class="icon-search"
        />
      </div>
    </div>
  </div>

  <PluginList v-if="!loading && pluginsToShow.length > 0"
              :plugins-to-show="pluginsToShow"
              :is-auto-update-possible="isAutoUpdatePossible"
              :is-super-user="isSuperUser"
              :is-multi-server-environment="isMultiServerEnvironment"
              :is-plugins-admin-enabled="isPluginsAdminEnabled"
              :is-valid-consumer="isValidConsumer"
              :deactivate-nonce="deactivateNonce"
              :activate-nonce="activateNonce"
              :install-nonce="installNonce"
              :update-nonce="updateNonce"
              @triggerUpdate="this.fetchPlugins()"
  />

  <ContentBlock v-if="!loading && pluginsToShow.length == 0">
    {{ translate(showThemes ? 'Marketplace_NoThemesFound' : 'Marketplace_NoPluginsFound') }}
  </ContentBlock>

  <ContentBlock v-if="loading">
    <img
      src="plugins/Morpheus/images/loading-blue.gif"
      :alt="translate('General_LoadingData')"
    />
    {{ loadingMessage }}
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import {
  translate, Matomo, MatomoUrl, AjaxHelper, ContentBlock,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import PluginList from '../PluginList/PluginList.vue';

interface MarketplaceState {
  loading: boolean;
  fetchRequest: Promise<void>|null;
  fetchRequestAbortController: AbortController|null;
  pluginSort: string;
  pluginTypeFilter: string;
  searchQuery: string;
  pluginsToShow: Array<Record<string, unknown>>;
}

const lcfirst = (s: string) => `${s[0].toLowerCase()}${s.substring(1)}`;

export default defineComponent({
  props: {
    pluginTypeOptions: {
      type: Object,
      required: true,
    },
    defaultSort: {
      type: String,
      required: true,
    },
    pluginSortOptions: {
      type: Object,
      required: true,
    },
    numAvailablePluginsByType: {
      type: Object,
      required: true,
    },
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    isMultiServerEnvironment: Boolean,
    installNonce: {
      type: String,
      required: true,
    },
    activateNonce: {
      type: String,
      required: true,
    },
    deactivateNonce: {
      type: String,
      required: true,
    },
    updateNonce: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    PluginList,
    Field,
  },
  data(): MarketplaceState {
    return {
      loading: false,
      fetchRequest: null,
      fetchRequestAbortController: null,
      pluginSort: this.defaultSort,
      pluginTypeFilter: 'plugins',
      searchQuery: '',
      pluginsToShow: [],
    };
  },
  mounted() {
    Matomo.postEvent('Marketplace.Marketplace.mounted', { element: this.$refs.root });
    watch(() => MatomoUrl.hashParsed.value, () => {
      this.updateValuesFromHash(false);
    });

    this.updateValuesFromHash(true);
  },
  unmounted() {
    Matomo.postEvent('Marketplace.Marketplace.unmounted', { element: this.$refs.root });
  },
  methods: {
    updateValuesFromHash(forceFetch: boolean) {
      let doFetch = forceFetch;

      const newSearchQuery = (MatomoUrl.hashParsed.value.query || '') as string;
      const newPluginSort = (MatomoUrl.hashParsed.value.sort || '') as string;
      const newPluginTypeFilter = (MatomoUrl.hashParsed.value.pluginType || '') as string;

      if (newSearchQuery || this.searchQuery) {
        doFetch = doFetch || newSearchQuery !== this.searchQuery;
        this.searchQuery = newSearchQuery;
      }

      if (newPluginSort) {
        doFetch = doFetch || newPluginSort !== this.pluginSort;
        this.pluginSort = newPluginSort;
      }

      if (newPluginTypeFilter) {
        doFetch = doFetch || newPluginTypeFilter !== this.pluginTypeFilter;
        this.pluginTypeFilter = newPluginTypeFilter;
      }

      if (!doFetch) {
        return;
      }

      this.fetchPlugins();
    },
    updateQuery(event: Event) {
      MatomoUrl.updateHash({
        ...MatomoUrl.hashParsed.value,
        query: event,
      });
    },
    updateType(event: Event) {
      MatomoUrl.updateHash({
        ...MatomoUrl.hashParsed.value,
        pluginType: event,
      });
    },
    updateSort(event: Event) {
      MatomoUrl.updateHash({
        ...MatomoUrl.hashParsed.value,
        sort: event,
      });
    },
    fetchPlugins() {
      this.loading = true;
      this.pluginsToShow = [];

      if (this.fetchRequestAbortController) {
        this.fetchRequestAbortController.abort();
        this.fetchRequestAbortController = null;
      }

      this.fetchRequestAbortController = new AbortController();
      this.fetchRequest = AjaxHelper.post(
        {
          module: 'Marketplace',
          action: 'searchPlugins',
          format: 'JSON',
        },
        {
          query: this.searchQuery,
          sort: this.pluginSort,
          themesOnly: this.showThemes,
          purchaseType: this.pluginTypeFilter === 'premium' ? 'paid' : '',
        },
        {
          withTokenInUrl: true,
          abortController: this.fetchRequestAbortController,
        },
      ).then((response) => {
        this.pluginsToShow = response;
      }).finally(() => {
        this.loading = false;
        this.fetchRequestAbortController = null;
      });
    },
  },
  computed: {
    queryInputTitle(): string {
      const plugins = lcfirst(translate('General_Plugins'));
      const pluginCount = this.numAvailablePluginsByType[this.pluginTypeFilter] || 0;
      return `${translate('General_Search')} ${pluginCount} ${plugins}...`;
    },
    loadingMessage(): string {
      return translate(
        'Mobile_LoadingReport',
        translate(this.showThemes ? 'CorePluginsAdmin_Themes' : 'General_Plugins'),
      );
    },
    showThemes(): boolean {
      return this.pluginTypeFilter === 'themes';
    },
  },
});
</script>

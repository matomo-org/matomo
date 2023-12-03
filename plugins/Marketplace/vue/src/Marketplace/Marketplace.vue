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
        @update:model-value="pluginTypeFilter = $event; changePluginType()"
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
        @update:model-value="pluginSort = $event; changePluginSort()"
        :title="translate('Marketplace_Sort')"
        :full-width="true"
        :options="pluginSortOptions"
      >
      </Field>
    </div>
    <!-- Hide filters and search for themes because we don't have many of them -->
    <div class="col s12 m12 l4 " v-if="pluginsToShow?.length > 20 || query">
      <form
        method="post"
        class="plugin-search"
        :action="pluginSearchFormAction"
        ref="pluginSearchForm"
      >
        <div>
          <Field
            uicontrol="text"
            name="query"
            :title="queryInputTitle"
            :full-width="true"
            v-model="searchQuery"
          >
          </Field>
        </div>
        <span
          class="icon-search"
          @click="$refs.pluginSearchForm.submit()"
        />
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, MatomoUrl, Matomo } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface MarketplaceState {
  pluginSort: string;
  pluginTypeFilter: string;
  searchQuery: string;
}

const lcfirst = (s: string) => `${s[0].toLowerCase()}${s.substring(1)}`;

export default defineComponent({
  props: {
    pluginType: {
      type: String,
      required: true,
    },
    pluginTypeOptions: {
      type: [Object, Array],
      required: true,
    },
    sort: {
      type: String,
      required: true,
    },
    pluginSortOptions: {
      type: [Object, Array],
      required: true,
    },
    pluginsToShow: {
      type: Array,
      required: true,
    },
    query: {
      type: String,
      default: '',
    },
    numAvailablePlugins: {
      type: Number,
      required: true,
    },
  },
  components: {
    Field,
  },
  data(): MarketplaceState {
    return {
      pluginSort: this.sort,
      pluginTypeFilter: this.pluginType,
      searchQuery: this.query,
    };
  },
  mounted() {
    Matomo.postEvent('Marketplace.Marketplace.mounted', { element: this.$refs.root });
  },
  unmounted() {
    Matomo.postEvent('Marketplace.Marketplace.unmounted', { element: this.$refs.root });
  },
  methods: {
    changePluginSort() {
      MatomoUrl.updateUrl(
        {
          ...MatomoUrl.urlParsed.value,
          query: '',
          sort: this.pluginSort,
        },
        {
          ...MatomoUrl.hashParsed.value,
          query: '',
          sort: this.pluginSort,
        },
      );
    },
    changePluginType() {
      MatomoUrl.updateUrl(
        {
          ...MatomoUrl.urlParsed.value,
          query: '',
          show: this.pluginTypeFilter,
        },
        {
          ...MatomoUrl.hashParsed.value,
          query: '',
          show: this.pluginTypeFilter,
        },
      );
    },
  },
  computed: {
    pluginSearchFormAction(): string {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        sort: '',
        embed: '0',
      })}#?${MatomoUrl.stringify({
        ...MatomoUrl.hashParsed.value,
        sort: '',
        embed: '0',
        query: this.searchQuery,
      })}`;
    },
    queryInputTitle(): string {
      const plugins = lcfirst(translate('General_Plugins'));
      return `${translate('General_Search')} ${this.numAvailablePlugins} ${plugins}...`;
    },
  },
});
</script>

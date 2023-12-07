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
import { defineComponent, nextTick } from 'vue';
import {
  debounce, translate, MatomoUrl, Matomo,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface MarketplaceState {
  pluginSort: string;
  pluginTypeFilter: string;
  searchQuery: string;
}

const lcfirst = (s: string) => `${s[0].toLowerCase()}${s.substring(1)}`;

const { $ } = window;

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
  created() {
    const addCardClickHandler = (selector: string) => {
      const $nodes = $(selector);
      if (!$nodes || !$nodes.length) {
        return;
      }

      $nodes.each((index, node) => {
        const $card = $(node);

        $card.off('click.cardClick');
        $card.on('click.cardClick', (event) => {
          // check if the target is a link or is a descendant of a link
          // to skip direct clicks on links within the card, we want those honoured
          if ($(event.target).closest('a').length) {
            return;
          }

          const $titleLink = $card.find('a.card-title-link');
          if ($titleLink) {
            event.stopPropagation();
            $titleLink.trigger('click');
          }
        });
      });
    };

    const shrinkDescriptionIfMultilineTitle = debounce((selector: string) => {
      const $nodes = $(selector);
      if (!$nodes || !$nodes.length) {
        return;
      }

      $nodes.each((index, node) => {
        const $card = $(node);
        const $titleText = $card.find('.card-title');

        if ($titleText) {
          let lines = 1;
          const elHeight = +$titleText.height()!;
          const lineHeight = +$titleText.css('line-height').replace('px', '');
          if (lineHeight) {
            lines = Math.ceil(elHeight / lineHeight) ?? 1;
          }

          const $cardDescription = $card.find('.card-description');
          if (lines > 1) {
            $cardDescription.addClass('card-description-clamped');
          } else {
            $cardDescription.removeClass('card-description-clamped');
          }
        }
      });
    }, 100);

    nextTick(() => {
      const cardSelector = '.marketplace .card-holder';

      addCardClickHandler(cardSelector);
      shrinkDescriptionIfMultilineTitle(cardSelector);

      $(window).resize(() => {
        shrinkDescriptionIfMultilineTitle(cardSelector);
      });
    });
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

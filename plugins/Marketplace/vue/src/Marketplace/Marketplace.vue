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
        const $alertText = $card.find('.card-content-bottom .alert');
        const hasDownloads = $card.hasClass('card-with-downloads');

        let titleLines = 1;
        if ($titleText.length) {
          const elHeight = +$titleText.height()!;
          const lineHeight = +$titleText.css('line-height').replace('px', '');
          if (lineHeight) {
            titleLines = Math.ceil(elHeight / lineHeight) ?? 1;
          }
        }

        let alertLines = 0;
        if ($alertText.length) {
          const elHeight = +$alertText.height()!;
          const lineHeight = +$alertText.css('line-height').replace('px', '');
          if (lineHeight) {
            alertLines = Math.ceil(elHeight / lineHeight) ?? 1;
          }
        }

        const $cardDescription = $card.find('.card-description');
        if ($cardDescription.length) {
          const cardDescription = $cardDescription[0] as HTMLElement;
          let clampedLines = 0;
          // a bit convoluted logic, but this is what's been arrived at with a designer
          // and via testing in browser
          //
          // a) visible downloads count
          //    -> clamp to 2 lines if title is 2 lines or more or alert is 2 lines or more
          //       or together are more than 3 lines
          //    -> clamp to 1 line if title is over 2 lines and alert is over 2 lines simultaneously
          // b) no downloads count (i.e. a premium plugin)
          //    -> clamp to 2 lines if sum of lines for title and notification is over 4
          if (hasDownloads) {
            if ((titleLines >= 2 || alertLines >= 2) || (titleLines + alertLines > 3)) {
              clampedLines = 2;
            }
            if (titleLines + alertLines > 3) {
              clampedLines = 1;
            }
          } else if (titleLines + alertLines > 4) {
            clampedLines = 2;
          }

          if (clampedLines) {
            cardDescription.setAttribute('data-clamp', `${clampedLines}`);
          } else {
            cardDescription.removeAttribute('data-clamp');
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

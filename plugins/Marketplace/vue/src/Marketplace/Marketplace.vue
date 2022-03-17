<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<todo>
- test in UI
- create PR
</todo>

<template>
  <div class="row marketplaceActions">
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
    <div class="col s12 m12 l4 " v-if="pluginsToShow.length > 20 || query">
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
import { defineComponent, onUnmounted } from 'vue';
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
  setup() {
    const onInstallAllPaidPlugins = () => {
      Matomo.helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce as HTMLElement);
    };

    Matomo.on('Marketplace.installAllPaidPlugins', onInstallAllPaidPlugins);

    onUnmounted(() => {
      Matomo.off('Marketplace.installAllPaidPlugins', onInstallAllPaidPlugins);
    });
  },
  created() {
    setTimeout(() => {

    });
    /*
                    $timeout(function () {


                        $('.installAllPaidPlugins').click(function (event) {
                            event.preventDefault();

                            piwikHelper.modalConfirm('#installAllPaidPluginsAtOnce');
                        });

                        // Keeps the plugin descriptions the same height
                        $('.marketplace .plugin .description').dotdotdot({
                            after: 'a.more',
                            watch: 'window'
                        });

                        piwik.helper.compileAngularComponents(element.find('[piwik-plugin-name]'));

                        function syncMaxHeight2 (selector) {

                            if (!selector) {
                                return;
                            }

                            var $nodes = $(selector);

                            if (!$nodes || !$nodes.length) {
                                return;
                            }

                            var maxh3 = null;
                            var maxMeta = null;
                            var maxFooter = null;
                            var nodesToUpdate = [];
                            var lastTop = 0;
                            $nodes.each(function (index, node) {
                                var $node = $(node);
                                var top   = $node.offset().top;

                                if (lastTop !== top) {
                                    nodesToUpdate = [];
                                    lastTop = top;
                                    maxh3 = null;
                                    maxMeta = null;
                                    maxFooter = null;
                                }

                                nodesToUpdate.push($node);

                                var heightH3 = $node.find('h3').height();
                                var heightMeta = $node.find('.metadata').height();
                                var heightFooter = $node.find('.footer').height();

                                if (!maxh3) {
                                    maxh3 = heightH3;
                                } else if (maxh3 < heightH3) {
                                    maxh3 = heightH3;
                                }

                                if (!maxMeta) {
                                    maxMeta = heightMeta;
                                } else if (maxMeta < heightMeta) {
                                    maxMeta = heightMeta;
                                }

                                if (!maxFooter) {
                                    maxFooter = heightFooter;
                                } else if (maxFooter < heightFooter) {
                                    maxFooter = heightFooter;
                                }

                                $.each(nodesToUpdate, function (index, $node) {
                                    if (maxh3) {
                                        $node.find('h3').height(maxh3 + 'px');
                                    }
                                    if (maxMeta) {
                                        $node.find('.metadata').height(maxMeta + 'px');
                                    }
                                    if (maxFooter) {
                                        $node.find('.footer').height(maxFooter + 'px');
                                    }
                                });
                            });
                        }
                        syncMaxHeight2('.marketplace .plugin');
     */
  },
  methods: {
    changePluginSort() {
      MatomoUrl.updateLocation({
        ...MatomoUrl.urlParsed.value,
        query: '',
        sort: this.pluginSort,
      });
    },
    changePluginType() {
      MatomoUrl.updateLocation({
        ...MatomoUrl.urlParsed.value,
        query: '',
        show: this.pluginType,
      });
    },
  },
  computed: {
    pluginSearchFormAction(): string {
      return MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        sort: '',
        embed: '0',
      });
    },
    queryInputTitle(): string {
      const plugins = lcfirst(translate('General_Plugins'));
      return `${translate('General_Search')} ${this.numAvailablePlugins} ${plugins}...`;
    },
  },
});
</script>

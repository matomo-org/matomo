<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="pluginListContainer row" v-if="pluginsToShow.length > 0">
    <div class="col s12 m6 l4" v-for="plugin in pluginsToShow" :key="plugin.name">
      <div :class="`card-holder ${plugin.numDownloads > 0 ? 'card-with-downloads' : '' }`">
        <div class="card">
          <div class="card-content">
            <img :src="`${plugin.coverImage}?w=880&h=480`" alt="" class="cover-image">
            <div class="content-container">
              <div class="card-content-top">
                <img v-if="'piwik' == plugin.owner || 'matomo-org' == plugin.owner"
                     class="matomo-badge matomo-badge-top"
                     src="plugins/Marketplace/images/matomo-badge.png"
                     aria-label="Matomo plugin"
                     alt=""
                />
                <div class="price">
                  <template v-if="plugin.priceFrom">
                    {{ translate('Marketplace_PriceFromPerPeriod',
                                 plugin.priceFrom.prettyPrice,
                                 plugin.priceFrom.period) }}
                  </template>
                  <template v-else-if="plugin.isFree">
                    {{ translate('Marketplace_Free') }}
                  </template>
                </div>
                <a v-plugin-name="{ pluginName: plugin.name }"
                   class="card-title-link" href="#" tabindex="7">
                  <div class="card-focus"></div>
                  <h2 class="card-title">{{ plugin.displayName }}<span
                    class="card-title-chevron">&nbsp;›</span></h2>
                </a>
                <div class="card-description">{{ plugin.description }}</div>
              </div>
              <div class="card-content-bottom">
                <div v-if="plugin.numDownloads > 0" class="downloads">
                  {{ plugin.numDownloadsPretty }} {{ translate('General_Downloads').toLowerCase() }}
                </div>
                <div class="cta-container">
                  <CTAContainer
                    :is-super-user="isSuperUser"
                    :is-plugins-admin-enabled="isPluginsAdminEnabled"
                    :is-multi-server-environment="isMultiServerEnvironment"
                    :is-valid-consumer="isValidConsumer"
                    :is-auto-update-possible="isAutoUpdatePossible"
                    :activate-nonce="activateNonce"
                    :deactivate-nonce="deactivateNonce"
                    :install-nonce="installNonce"
                    :update-nonce="updateNonce"
                    :plugin="plugin"
                  />
                </div>
                <img v-if="'piwik' == plugin.owner || 'matomo-org' == plugin.owner"
                     class="matomo-badge matomo-badge-bottom"
                     src="plugins/Marketplace/images/matomo-badge.png"
                     aria-label="Matomo plugin"
                     alt=""
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ContentBlock v-if="pluginsToShow.length == 0">
    {{ translate(showThemes ? 'Marketplace_NoThemesFound' : 'Marketplace_NoPluginsFound') }}
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, MatomoUrl } from 'CoreHome';
import { PluginName } from 'CorePluginsAdmin';
import CTAContainer from './CTAContainer.vue';

export default defineComponent({
  props: {
    pluginsToShow: {
      type: Array,
      required: true,
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true,
    },
    isSuperUser: {
      type: Boolean,
      required: true,
    },
    isValidConsumer: {
      type: Boolean,
      required: true,
    },
    isMultiServerEnvironment: {
      type: Boolean,
      required: true,
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true,
    },
    showThemes: {
      type: Boolean,
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
    installNonce: {
      type: String,
      required: true,
    },
    updateNonce: {
      type: String,
      required: true,
    },
  },
  components: {
    CTAContainer,
    ContentBlock,
  },
  directives: {
    PluginName,
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

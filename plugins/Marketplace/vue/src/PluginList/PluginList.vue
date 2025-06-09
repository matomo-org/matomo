<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <RequestTrial
    v-model="showRequestTrialForPlugin"
    @trialRequested="this.$emit('triggerUpdate')"
  />

  <StartFreeTrial
    :current-user-email="currentUserEmail"
    :is-valid-consumer="isValidConsumer"
    v-model="showStartFreeTrialForPlugin"
    @trialStarted="this.$emit('triggerUpdate');"
    @startTrialStart="this.$emit('startTrialStart');"
    @startTrialStop="this.$emit('startTrialStop');"
  />

  <PluginDetailsModal
    v-model="showPluginDetailsForPlugin"
    :is-super-user="isSuperUser"
    :is-plugins-admin-enabled="isPluginsAdminEnabled"
    :is-multi-server-environment="isMultiServerEnvironment"
    :is-valid-consumer="isValidConsumer"
    :is-auto-update-possible="isAutoUpdatePossible"
    :has-some-admin-access="hasSomeAdminAccess"
    :deactivate-nonce="deactivateNonce"
    :activate-nonce="activateNonce"
    :install-nonce="installNonce"
    :update-nonce="updateNonce"
    :num-users="numUsers"
    @requestTrial="this.requestTrial($event)"
    @startFreeTrial="this.startFreeTrial($event)"
  />

  <div class="pluginListContainer row" v-if="pluginsToShow.length > 0">
    <div class="col s12 m6 l4" v-for="plugin in pluginsToShow" :key="plugin.name">
      <div :class="`card-holder ${plugin.numDownloads > 0 ? 'card-with-downloads' : '' }`"
           @click="clickCard($event, plugin)">
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
                <a @click.prevent="clickCard($event, plugin)"
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
                <div class="owner">
                  {{translate('Marketplace_CreatedBy')}}
                  <span
                    v-if="plugin.owner === 'piwik' || plugin.owner === 'matomo-org'"> Matomo</span>
                  <span v-else> {{ plugin.owner }}</span>
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
                    :in-modal="false"
                    @openDetailsModal="this.openDetailsModal(plugin)"
                    @requestTrial="this.requestTrial(plugin)"
                    @startFreeTrial="this.startFreeTrial(plugin)"
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
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import { MatomoUrl } from 'CoreHome';
import CTAContainer from './CTAContainer.vue';
import RequestTrial from '../RequestTrial/RequestTrial.vue';
import StartFreeTrial from '../StartFreeTrial/StartFreeTrial.vue';
import PluginDetailsModal from '../PluginDetailsModal/PluginDetailsModal.vue';
import { TObject } from '../types';

const { $ } = window;

interface PluginListState {
  showRequestTrialForPlugin: TObject | null;
  showStartFreeTrialForPlugin: TObject | null;
  showPluginDetailsForPlugin: TObject | null;
}

export default defineComponent({
  props: {
    currentUserEmail: String,
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
    hasSomeAdminAccess: {
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
    numUsers: {
      type: Number,
      required: true,
    },
  },
  data(): PluginListState {
    return {
      showRequestTrialForPlugin: null,
      showStartFreeTrialForPlugin: null,
      showPluginDetailsForPlugin: null,
    };
  },
  components: {
    PluginDetailsModal,
    CTAContainer,
    RequestTrial,
    StartFreeTrial,
  },
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
  watch: {
    pluginsToShow(newValue, oldValue) {
      if (newValue && newValue !== oldValue) {
        this.shrinkDescriptionIfMultilineTitle();
        this.parseShowPluginParameter();
      }
    },
  },
  mounted() {
    $(window).resize(() => {
      this.shrinkDescriptionIfMultilineTitle();
    });
    watch(() => MatomoUrl.hashParsed.value.showPlugin, (newValue, oldValue) => {
      if (newValue && newValue !== oldValue) {
        this.parseShowPluginParameter();
      }
    });
    this.parseShowPluginParameter();
  },
  methods: {
    parseShowPluginParameter() {
      const { showPlugin, pluginType, query } = MatomoUrl.hashParsed.value;

      if (!showPlugin) {
        return;
      }

      const pluginToShow = this.pluginsToShow.filter(
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (plugin: any) => plugin.name === showPlugin,
      );
      if (pluginToShow.length === 1) {
        const [plugin] = pluginToShow as TObject[];

        this.openDetailsModal(plugin);
        this.scrollPluginCardIntoView(plugin);
      } else if (pluginType !== '' || query !== '') {
        // plugin was not found in current list, so unset filters to retry
        MatomoUrl.updateHash({
          ...MatomoUrl.hashParsed.value,
          pluginType: 'plugins',
          query: null,
        });
      }
    },
    shrinkDescriptionIfMultilineTitle() {
      const $nodes = $('.marketplace .card-holder');
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
            if ((titleLines >= 2 || alertLines > 2) || (titleLines + alertLines >= 4)) {
              clampedLines = 2;
            }
            if (titleLines + alertLines >= 5) {
              clampedLines = 1;
            }
          } else if (titleLines + alertLines >= 5) {
            clampedLines = 2;
          }

          if (clampedLines) {
            cardDescription.setAttribute('data-clamp', `${clampedLines}`);
          } else {
            cardDescription.removeAttribute('data-clamp');
          }
        }
      });
    },
    clickCard(event: MouseEvent, plugin: TObject) {
      // check if the target is a link or is a descendant of a link
      // to skip direct clicks on links within the card, we want those honoured
      if ($(event.target as HTMLElement).closest('a:not(.card-title-link)').length) {
        return;
      }

      event.stopPropagation();
      this.openDetailsModal(plugin);
    },
    openDetailsModal(plugin: TObject) {
      this.showPluginDetailsForPlugin = plugin;
    },
    scrollPluginCardIntoView(plugin: TObject) {
      const $titles = $(`.pluginListContainer .card-title:contains("${plugin.displayName}")`);

      if ($titles.length !== 1) {
        return;
      }

      const $cards = $titles.parents('.card');

      if ($cards.length !== 1 || !$cards[0].scrollIntoView) {
        return;
      }

      $cards[0].scrollIntoView({ block: 'start', behavior: 'smooth' });
    },
    requestTrial(plugin: TObject) {
      this.showRequestTrialForPlugin = plugin;
    },
    startFreeTrial(plugin: TObject) {
      this.showStartFreeTrialForPlugin = plugin;
    },
  },
});
</script>

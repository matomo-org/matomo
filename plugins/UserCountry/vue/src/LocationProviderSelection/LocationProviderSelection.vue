<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="locationProviderSelection">
    <div v-if="!isThereWorkingProvider" v-html="$sanitize(setUpGuides || '')"></div>
    <div class="row">
      <div class="col s12 push-m9 m3">{{ translate('General_InfoFor', thisIp) }}</div>
    </div>
    <div
      v-for="(provider, id) in visibleLocationProviders"
      :key="id"
      :class="`row form-group provider${id}`"
    >
      <div class="col s12 m4 l2">
        <p>
          <label>
            <input
              class="location-provider"
              name="location-provider"
              type="radio"
              :id="`provider_input_${id}`"
              :disabled="provider.status !== 1"
              :checked="selectedProvider === id"
              @change="selectedProvider = id"
            />
            <span>{{ translateOrDefault(provider.title) }}</span>
          </label>
        </p>
        <p class="loc-provider-status">
          <span v-if="provider.status === 0 " class="is-not-installed">
            {{ translate('General_NotInstalled') }}
          </span>
          <span v-else-if="provider.status === 1" class="is-installed">
            {{ translate('General_Installed') }}
          </span>
          <span v-else-if="provider.status === 2" class="is-broken">
            {{ translate('General_Broken') }}
          </span>
        </p>
      </div>
      <div class="col s12 m4 l6">
        <p v-html="$sanitize(translateOrDefault(provider.description))"></p>
        <p
          v-if="provider.status !== 1 && provider.install_docs"
          v-html="$sanitize(provider.install_docs)"
        ></p>
      </div>
      <div class="col s12 m4 l4">
        <div class="form-help" v-if="provider.status === 1">
          <div v-if="thisIp !== '127.0.0.1' && thisIp !== '::1'">
            {{ translate('UserCountry_CurrentLocationIntro') }}:
            <div>
              <br />
              <div style="position: absolute;">
                <ActivityIndicator
                  :loading="updateLoading[id]"
                />
              </div>
              <span
                class="location"
                :style="{ visibility: providerLocations[id] ? 'visible' : 'hidden'}"
              >
                <strong v-html="$sanitize(providerLocations[id] || '&nbsp;')"/>
              </span>
            </div>
            <div class="text-right">
              <a
                @click.prevent="refreshProviderInfo(id)"
              >{{ translate('General_Refresh') }}</a>
            </div>
          </div>
          <div v-else>
            {{ translate('UserCountry_CannotLocalizeLocalIP', thisIp) }}
          </div>
        </div>
        <div class="form-help" v-if="provider.statusMessage">
          <strong v-if="provider.status === 2">{{ translate('General_Error') }}:</strong>
          <span v-html="$sanitize(provider.statusMessage)"/>
        </div>
        <div
          class="form-help"
          v-if="provider.extra_message"
          v-html="$sanitize(provider.extra_message)"
        >
        </div>
      </div>
    </div>
    <div v-if="!Object.keys(locationProvidersNotDefaultOrDisabled).length">
      <Notification
        :noclear="true"
        context="warning"
      >
        <span v-html="$sanitize(noProvidersText)"></span>
      </Notification>
    </div>
    <SaveButton
      @confirm="save()"
      :saving="isLoading"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  NotificationsStore,
  ActivityIndicator,
  Notification,
} from 'CoreHome';
import { SaveButton } from 'CorePluginsAdmin';

interface ProviderInfo {
  id: string;
  isVisible: boolean;
  description: string;
  status: number;
  install_docs?: string;
  extra_message?: string;
  location?: string;
}

interface LocationProviderSelectionState {
  isLoading: boolean;
  updateLoading: Record<string, boolean>;
  selectedProvider: string;
  statusMessage?: string;
  providerLocations: Record<string, string>;
}

export default defineComponent({
  props: {
    currentProviderId: {
      type: String,
      required: true,
    },
    isThereWorkingProvider: Boolean,
    setUpGuides: String,
    thisIp: {
      type: String,
      required: true,
    },
    locationProviders: {
      type: Object,
      required: true,
    },
    defaultProviderId: {
      type: String,
      required: true,
    },
    disabledProviderId: {
      type: String,
      required: true,
    },
  },
  components: {
    ActivityIndicator,
    Notification,
    SaveButton,
  },
  data(): LocationProviderSelectionState {
    return {
      isLoading: false,
      updateLoading: {},
      selectedProvider: this.currentProviderId,
      providerLocations: Object.fromEntries(
        Object.entries(this.locationProviders).map(([k, p]) => [k, p.location]),
      ),
    };
  },
  methods: {
    refreshProviderInfo(providerId: string) {
      // this should not be in a controller... ideally we fetch this data always from client side
      // and do not prefill it server side
      this.updateLoading[providerId] = true;

      delete this.providerLocations[providerId];

      AjaxHelper.fetch<string>(
        {
          module: 'UserCountry',
          action: 'getLocationUsingProvider',
          id: providerId,
          format: 'html',
        },
        {
          format: 'html',
        },
      ).then((response) => {
        this.providerLocations[providerId] = response;
      }).finally(() => {
        this.updateLoading[providerId] = false;
      });
    },
    save() {
      if (!this.selectedProvider) {
        return;
      }

      this.isLoading = true;
      AjaxHelper.fetch(
        {
          method: 'UserCountry.setLocationProvider',
          providerId: this.selectedProvider,
        },
        {
          withTokenInUrl: true,
        },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('General_Done'),
          context: 'success',
          noclear: true,
          type: 'toast',
          id: 'userCountryLocationProvider',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    },
  },
  computed: {
    visibleLocationProviders() {
      return Object.fromEntries(
        Object.entries(this.locationProviders as ProviderInfo[]).filter(([, p]) => p.isVisible),
      );
    },
    locationProvidersNotDefaultOrDisabled() {
      return Object.fromEntries(
        Object.entries(this.locationProviders as ProviderInfo[]).filter(
          ([, p]) => p.id !== this.defaultProviderId && p.id !== this.disabledProviderId,
        ),
      );
    },
    noProvidersText() {
      return translate(
        'UserCountry_NoProviders',
        '<a rel="noreferrer noopener" href="https://db-ip.com/?refid=mtm" target="_blank">',
        '</a>',
      );
    },
  },
});
</script>

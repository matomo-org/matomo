<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="detectionPage">
    <ContentBlock :content-title="translate('DevicesDetection_DeviceDetection')">
      <form action="" method="POST">

        <h3>{{ translate('DevicesDetection_UserAgent') }}</h3>

        <textarea name="ua" v-model="userAgentText"></textarea>
        <br />

        <h3>{{ translate('DevicesDetection_ClientHints') }}</h3>

        <span class="checkbox-container usech" v-if="isClientHintsSupported">
            <label>
                <input
                  type="checkbox"
                  id="usech"
                  v-model="considerClientHints"
                  @change="toggleClientHints()"
                />
                <span>{{ translate('DevicesDetection_ConsiderClientHints') }}</span>
            </label>
        </span>

        <textarea
          name="clienthints" style="margin-top: 2em;"
          v-if="isClientHintsSupported && considerClientHints"
          v-model="clientHintsText"
        ></textarea>

        <span id="noclienthints" class="alert alert-warning" v-show="!isClientHintsSupported">
          {{ translate('DevicesDetection_ClientHintsNotSupported') }}
        </span>

        <br /><br />
        <input type="submit" :value="translate('General_Refresh')" class="btn" />
      </form>

      <h3 v-if="bot_info">{{ translate('DevicesDetection_BotDetected', bot_info.name) }}</h3>

      <div v-else>
      <h3>{{ translate('DevicesDetection_ColumnOperatingSystem') }}</h3>
        <table class="detection" v-content-table>
          <tbody>
          <tr>
            <td>
              {{ translate('General_Name') }}
              <small>
                (<a href="" @click.prevent="showList('os')">{{ translate('Mobile_ShowAll') }}</a>)
              </small>
            </td>
            <td><img :height="16" :width="16" :src="os_logo" />{{ os_name }}</td>
          </tr>
          <tr>
            <td>{{ translate('CorePluginsAdmin_Version') }}</td>
            <td>{{ os_version }}</td>
          </tr>
          <tr>
            <td>
              {{ translate('DevicesDetection_OperatingSystemFamily') }}
              <small>
                (<a
                  href=""
                  @click.prevent="showList('osfamilies')"
                >{{ translate('Mobile_ShowAll') }}</a>)
              </small>
            </td>
            <td><img :height="16" :width="16" :src="os_family_logo" />{{ os_family }}</td>
          </tr>
          </tbody>
        </table>

        <h3>{{ translate('DevicesDetection_ColumnBrowser') }}</h3>
        <table class="detection" v-content-table>
          <tbody>
          <tr>
            <td>
              {{ translate('General_Name') }}
              <small>
                (<a
                  href=""
                  @click.prevent="showList('browsers')"
                >{{ translate('Mobile_ShowAll') }}</a>)
              </small>
            </td>
            <td>
              <img :height="16" :width="16" :src="browser_logo" />{{ browser_name }}
            </td>
          </tr>
          <tr>
            <td>{{ translate('CorePluginsAdmin_Version') }}</td>
            <td>{{ browser_version }}</td>
          </tr>
          <tr>
            <td>
              {{ translate('DevicesDetection_BrowserFamily') }}
              <small>
                (<a
                  href=""
                  @click.prevent="showList('browserfamilies')"
                >{{ translate('Mobile_ShowAll') }}</a>)
              </small>
            </td>
            <td>
              <img :height="16" :width="16" :src="browser_family_logo" />{{ browser_family }}
            </td>
          </tr>
          </tbody>
        </table>

        <h3>{{ translate('DevicesDetection_Device') }}</h3>
        <table class="detection" v-content-table>
          <tbody>
          <tr>
            <td>
              {{ translate('DevicesDetection_dataTableLabelTypes') }}
              <small>
                (<a
                  href=""
                  @click.prevent="showList('devicetypes')"
                >{{ translate('Mobile_ShowAll') }}</a>)
              </small>
            </td>
            <td><img :height="16" :width="16" :src="device_type_logo" />{{ device_type }}</td>
          </tr>
          <tr>
            <td>
              {{ translate('DevicesDetection_dataTableLabelBrands') }}
              <small>
                (<a
                  href=""
                  @click.prevent="showList('brands')"
                >{{ translate('Mobile_ShowAll') }}</a>)
              </small>
            </td>
            <td><img :height="16" :width="16" :src="device_brand_logo" />{{ device_brand }}</td>
          </tr>
          <tr>
            <td>{{ translate('DevicesDetection_dataTableLabelModels') }}</td>
            <td>{{ device_model }}</td>
          </tr></tbody>
        </table>
      </div>
    </ContentBlock>

    <div class="ui-confirm" id="deviceDetectionItemList" ref="deviceDetectionItemList">
      <div class="itemList" v-html="$sanitize(itemListHtml)"></div>
      <input role="close" type="button" :value="translate('General_Close')"/>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  ContentBlock,
  ContentTable,
  AjaxHelper,
} from 'CoreHome';

interface DetectionPageState {
  itemListHtml: string;
  considerClientHints: boolean;
  clientHintsText: string;
  userAgentText: string;
  defaultClientHints: Record<string, string>|null;
}

function isClientHintsSupported() {
  const nav = navigator as any; // eslint-disable-line @typescript-eslint/no-explicit-any
  return nav.userAgentData && typeof nav.userAgentData.getHighEntropyValues === 'function';
}

let clientHints: Record<string, string>|null = null;

function getDefaultClientHints(): Promise<Record<string, string>|null> {
  const nav = navigator as any; // eslint-disable-line @typescript-eslint/no-explicit-any

  if (!isClientHintsSupported()) {
    return Promise.resolve(null);
  }

  if (clientHints) {
    return Promise.resolve(clientHints!);
  }

  // Initialize with low entropy values that are always available
  clientHints = {
    brands: nav.userAgentData.brands,
    platform: nav.userAgentData.platform,
  };

  // try to gather high entropy values
  // currently this methods simply returns the requested values through a Promise
  // In later versions it might require a user permission
  return nav.userAgentData.getHighEntropyValues(
    ['brands', 'model', 'platform', 'platformVersion', 'uaFullVersion', 'fullVersionList'],
  ).then((ua: any) => { // eslint-disable-line @typescript-eslint/no-explicit-any
    clientHints = { ...ua };

    if (clientHints!.fullVersionList) {
      // if fullVersionList is available, brands and uaFullVersion isn't needed
      delete clientHints!.brands;
      delete clientHints!.uaFullVersion;
    }

    return clientHints!;
  });
}

export default defineComponent({
  props: {
    userAgent: {
      type: String,
      required: true,
    },
    bot_info: Object,
    os_logo: String,
    os_name: String,
    os_version: String,
    os_family_logo: String,
    os_family: String,
    browser_logo: String,
    browser_name: String,
    browser_version: String,
    browser_family: String,
    browser_family_logo: String,
    device_type_logo: String,
    device_type: String,
    device_brand_logo: String,
    device_brand: String,
    device_model: String,
    clientHintsChecked: Boolean,
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentTable,
  },
  created() {
    getDefaultClientHints().then((hints) => {
      this.defaultClientHints = hints;
      this.toggleClientHints();
    });
  },
  data(): DetectionPageState {
    return {
      itemListHtml: '',
      considerClientHints: !!this.clientHintsChecked,
      clientHintsText: '',
      userAgentText: this.userAgent,
      defaultClientHints: null,
    };
  },
  methods: {
    showList(type: string) {
      AjaxHelper.fetch<string>(
        {
          module: 'DevicesDetection',
          action: 'showList',
          type,
        },
        {
          format: 'html',
        },
      ).then((response) => {
        this.itemListHtml = response;
        Matomo.helper.modalConfirm(
          this.$refs.deviceDetectionItemList as HTMLElement,
          undefined,
          { fixedFooter: true },
        );
      });
    },
    toggleClientHints() {
      if (this.considerClientHints && this.defaultClientHints !== null) {
        this.clientHintsText = this.clientHintsText || JSON.stringify(this.defaultClientHints);
      } else {
        this.clientHintsText = '';
      }
    },
  },
  computed: {
    isClientHintsSupported() {
      return isClientHintsSupported();
    },
  },
});
</script>

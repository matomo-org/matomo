<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="detectionPage">
    <ContentBlock :content-title="translate('DevicesDetection_DeviceDetection')">
      <h3>{{ translate('DevicesDetection_UserAgent') }}</h3>

      <form action="" method="POST">
        <textarea name="ua" :value="userAgent"></textarea>
        <br />
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
            <td><img height="16px" width="16px" :src="os_logo" />{{ os_name }}</td>
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
            <td><img height="16px" width="16px" :src="os_family_logo" />{{ os_family }}</td>
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
              <img height="16px" width="16px" :src="browser_logo" />{{ browser_name }}
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
              <img height="16px" width="16px" :src="browser_family_logo" />{{ browser_family }}
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
            <td><img height="16px" width="16px" :src="device_type_logo" />{{ device_type }}</td>
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
            <td><img height="16px" width="16px" :src="device_brand_logo" />{{ device_brand }}</td>
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
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentTable,
  },
  data(): DetectionPageState {
    return {
      itemListHtml: '',
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
  },
});
</script>

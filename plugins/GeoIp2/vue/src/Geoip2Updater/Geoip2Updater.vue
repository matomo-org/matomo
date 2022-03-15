<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- look over template
- look over component code
- get to build
- test in UI
- check uses:
  ./plugins/GeoIp2/templates/configuration.twig
  ./plugins/GeoIp2/angularjs/geoip2-updater/geoip2-updater.directive.js
- create PR
</todo>

<template>
  <div v-if="showGeoIPUpdateSection">
    <div v-if="!geoipDatabaseInstalled">
      <div v-show="showPiwikNotManagingInfo">
        <h3 />
        <div id="manage-geoip-dbs">
          <div
            class="row"
            id="geoipdb-screen1"
          >
            <div class="geoipdb-column-1 col s6">
              <p>{{ translate('GeoIp2_IWantToDownloadFreeGeoIP') }}<sup><small>*</small></sup></p>
            </div>
            <div class="geoipdb-column-2 col s6">
              <p />
            </div>
            <div class="geoipdb-column-1 col s6">
              <input
                type="button"
                class="btn"
                @click="startDownloadFreeGeoIp()"
                :value="`${translate('General_GetStarted')}...`"
              />
            </div>
            <div class="geoipdb-column-2 col s6">
              <input
                type="button"
                class="btn"
                id="start-automatic-update-geoip"
                @click="startAutomaticUpdateGeoIp()"
                :value="`${translate('General_GetStarted')}...`"
              />
            </div>
          </div>
          <div class="row">
            <p><sup>* <small>.</small></sup></p>
          </div>
        </div>
      </div>
      <div
        id="geoipdb-screen2-download"
        v-show="showFreeDownload"
      >
        <div>
          <Progressbar
            label
            :progress="progressFreeDownload"
          >
          </Progressbar>
        </div>
      </div>
    </div>

    <div
      id="geoipdb-update-info"
      v-if="geoipDatabaseInstalled"
    >
      <p>
        <br /><br />
        <span v-if="!!dbipLiteUrl"><br /><br /></span>
        <span v-show="geoipDatabaseInstalled">
          <br /><br />{{ translate('GeoIp2_GeoIPUpdaterIntro') }}:
        </span>
      </p>
      <div>
        <Field
          uicontrol="text"
          name="geoip-location-db"
          introduction
          data-title
          inline-help
          v-model="locationDbUrl"
          :value="geoIPLocUrl"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="text"
          name="geoip-isp-db"
          introduction
          data-title
          :inline-help="providerPluginHelp"
          v-model="ispDbUrl"
          :disabled="!isProviderPluginActive"
          :value="geoIPIspUrl"
        >
        </Field>
      </div>
      <div
        id="locationProviderUpdatePeriodInlineHelp"
        class="inline-help-node"
      >
        <span v-if="lastTimeUpdaterRun">
          {{ translate('GeoIp2_UpdaterWasLastRun', lastTimeUpdaterRun) }}
        </span>
        <span v-else>{{ translate('GeoIp2_UpdaterHasNotBeenRun') }}</span>
        <br /><br />
        <div id="geoip-updater-next-run-time" v-html="$sanitize(nextRunTimeText)">
        </div>
      </div>
      <div>
        <Field
          uicontrol="radio"
          name="geoip-update-period"
          introduction
          inline-help="#locationProviderUpdatePeriodInlineHelp"
          v-model="updatePeriod"
          :options="json_encode(updatePeriodOptions)"
        >
        </Field>
      </div>
      <input
        type="button"
        class="btn"
        @click="saveGeoIpLinks()"
        :value="buttonUpdateSaveText"
      />
      <div>
        <div id="done-updating-updater" />
        <div id="geoipdb-update-info-error" />
        <div>
          <Progressbar
            v-show="isUpdatingGeoIpDatabase"
            :progress="progressUpdateDownload"
            :label="progressUpdateLabel"
          />
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    <p class="form-description">{{ translate('GeoIp2_CannotSetupGeoIPAutoUpdating') }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  translate,
  AjaxHelper,
  Progressbar
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';


interface Geoip2UpdaterState {
  showFreeDownload: boolean;
  showPiwikNotManagingInfo: boolean;
  progressFreeDownload: number;
  progressUpdateDownload: number;
  buttonUpdateSaveText: string;
  progressUpdateLabel: string;
  locationDbUrl: string;
  ispDbUrl: string;
  orgDbUrl: string;
  updatePeriod: string;
  isUpdatingGeoIpDatabase: boolean;
}

export default defineComponent({
  props: {
    geoipDatabaseStartedInstalled: {
      type: Boolean, // TODO angularjs adapter
      required: true,
    },
    showGeoIPUpdateSection: {
      type: Boolean, // TODO angularjs adapter
      required: true,
    },
    dbipLiteUrl: {
      type: String,
      required: true,
    },
    dbipLiteFilename: {
      type: String,
      required: true,
    },
    geoIPLocUrl: {
      type: String,
      required: true,
    },
    isProviderPluginActive: {
      type: Boolean, // TODO: angularjs
      required: true,
    },
    geoIPIspUrl: {
      type: String,
      required: true,
    },
    lastTimeUpdaterRun: String,
    geoIPUpdatePeriod: String,
    updatePeriodOptions: {
      type: Object,
      required: true,
    },
    nextRunTime: Number,
    nextRunTimePretty: String,
  },
  components: {
    Progressbar,
    Field,
  },
  data(): Geoip2UpdaterState {
    return {
      geoipDatabaseInstalled: this.geoipDatabaseStartedInstalled,
      showFreeDownload: false,
      showPiwikNotManagingInfo: true,
      progressFreeDownload: 0,
      progressUpdateDownload: 0,
      buttonUpdateSaveText: translate('General_Save'),
      progressUpdateLabel: '',
      locationDbUrl: '',
      ispDbUrl: '',
      orgDbUrl: '',
      updatePeriod: this.geoIPUpdatePeriod || 'month',
      isUpdatingGeoIpDatabase: false,
    };
  },
  methods: {
    // TODO
    startDownloadFreeGeoIp() {
      this.showFreeDownload = true;
      this.showPiwikNotManagingInfo = false;
      this.progressFreeDownload = 0; // start download of free dbs

      this.downloadNextChunk('downloadFreeDBIPLiteDB', 'geoipdb-screen2-download', 'progressFreeDownload', false, {}, (response) => {
        if (response.error) {
          $('#geoipdb-update-info').html(response.error);
          this.geoipDatabaseInstalled = true;
        } else {
          window.location.reload();
        }
      });
    },
    // TODO
    startAutomaticUpdateGeoIp() {
      this.buttonUpdateSaveText = translate('General_Continue');
      this.showGeoIpUpdateInfo();
    },
    // TODO
    showGeoIpUpdateInfo() {
      this.geoipDatabaseInstalled = true; // todo we need to replace this the proper way eventually

      $('#geoip-db-mangement .card-title').text(translate('GeoIp2_SetupAutomaticUpdatesOfGeoIP'));
    },
    // TODO
    saveGeoIpLinks() {
      const currentDownloading = null;

      const updateGeoIPSuccess = (response) => {
        if (response && response.error) {
          this.isUpdatingGeoIpDatabase = false;

          const UI = require('piwik/UI');

          const notification = new UI.Notification();
          notification.show(response.error, {
            placeat: '#geoipdb-update-info-error',
            context: 'error',
            style: {
              display: 'inline-block'
            },
            id: 'userCountryGeoIpUpdate'
          });
        } else if (response && response.to_download) {
          const continuing = currentDownloading == response.to_download;
          currentDownloading = response.to_download; // show progress bar w/ message

          this.progressUpdateDownload = 0;
          this.progressUpdateLabel = response.to_download_label;
          this.isUpdatingGeoIpDatabase = true; // start/continue download

          this.downloadNextChunk('downloadMissingGeoIpDb', 'geoipdb-update-info', 'progressUpdateDownload', continuing, {
            key: response.to_download
          }, updateGeoIPSuccess);
        } else {
          this.progressUpdateLabel = '';
          this.isUpdatingGeoIpDatabase = false;

          const UI = require('piwik/UI');

          const notification = new UI.Notification();
          notification.show(translate('General_Done'), {
            placeat: '#done-updating-updater',
            context: 'success',
            noclear: true,
            type: 'toast',
            style: {
              display: 'inline-block'
            },
            id: 'userCountryGeoIpUpdate'
          });
          $('#geoip-updater-next-run-time').html(response.nextRunTime).parent().effect('highlight', {
            color: '#FFFFCB'
          }, 2000);
        }
      };

      AjaxHelper.withTokenInUrl();
      AjaxHelper.post({
        period: this.updatePeriod,
        module: 'GeoIp2',
        action: 'updateGeoIPLinks'
      }, {
        loc_db: this.locationDbUrl,
        isp_db: this.ispDbUrl,
        org_db: this.orgDbUrl
      }).then(updateGeoIPSuccess);
    },
    // TODO
    downloadNextChunk(action, thisId, progressBarId, cont, extraData, callback) {
      const data = {};

      for (const k in extraData) {
        data[k] = extraData[k];
      }

      AjaxHelper.withTokenInUrl();
      AjaxHelper.post({
        module: 'GeoIp2',
        action: action,
        'continue': cont ? 1 : 0
      }, data).then((response) => {
        if (!response || response.error) {
          callback(response);
        } else {
          // update progress bar
          const newProgressVal = Math.floor(response.current_size / response.expected_file_size * 100);
          this[progressBarId] = Math.min(newProgressVal, 100); // if incomplete, download next chunk, otherwise, show updater manager

          if (newProgressVal < 100) {
            this.downloadNextChunk(action, thisId, progressBarId, true, extraData, callback);
          } else {
            callback(response);
          }
        }
      }, () => {
        callback({
          error: translate('GeoIp2_FatalErrorDuringDownload')
        });
      });
    },
  },
  computed: {
    nextRunTimeText() {
      if (!this.nextRunTime) {
        return translate('GeoIp2_UpdaterIsNotScheduledToRun');
      }

      if (this.nextRunTime < Date.now()) { // TODO: check it works for UTC?
        return translate('GeoIp2_UpdaterScheduledForNextRun');
      } else {
        return translate(
          'GeoIp2_UpdaterWillRunNext',
          `<strong>${this.nextRunTimePretty}</strong>`,
        );
      }
    },
    providerPluginHelp() {
      if (this.isProviderPluginActive) {
        return undefined;
      }

      const text = translate('GeoIp2_ISPRequiresProviderPlugin');
      return `<div class='alert alert-warning'>${text}</div>`;
    },
  },
});
</script>

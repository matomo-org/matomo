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
  <ContentBlock :content-title="contentTitle" id="geoip-db-mangement">
    <div v-if="showGeoIpUpdateSection">
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
        v-if="geoipDatabaseInstalled && !downloadErrorMessage"
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
            :value="geoIpLocUrl"
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
            :value="geoIpIspUrl"
          >
          </Field>
        </div>
        <div
          id="locationProviderUpdatePeriodInlineHelp"
          class="inline-help-node"
          ref="inlineHelpNode"
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
            :options="updatePeriodOptions"
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
      <div v-if="downloadErrorMessage" v-html="$sanitize(downloadErrorMessage)"></div>
    </div>
    <div v-else>
      <p class="form-description">{{ translate('GeoIp2_CannotSetupGeoIPAutoUpdating') }}</p>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Progressbar,
  ContentBlock,
  NotificationsStore,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface Geoip2UpdaterState {
  geoipDatabaseInstalled: boolean;
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
  downloadErrorMessage: string|null;
  nextRunTimePrettyUpdated: string|null;
}

interface UpdateGeoIpLinksResponse {
  to_download?: string;
  to_download_label?: string;
  nextRunTime: string;
}

interface DownloadChunkResponse {
  current_size: number;
  expected_file_size: number;
}

const { $ } = window;

export default defineComponent({
  props: {
    geoipDatabaseStartedInstalled: {
      type: Boolean,
      required: true,
    },
    showGeoIpUpdateSection: {
      type: Boolean,
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
    geoIpLocUrl: {
      type: String,
      required: true,
    },
    isProviderPluginActive: {
      type: Boolean,
      required: true,
    },
    geoIpIspUrl: {
      type: String,
      required: true,
    },
    lastTimeUpdaterRun: String,
    geoIpUpdatePeriod: String,
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
    ContentBlock,
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
      updatePeriod: this.geoIpUpdatePeriod || 'month',
      isUpdatingGeoIpDatabase: false,
      downloadErrorMessage: null,
      nextRunTimePrettyUpdated: null,
    };
  },
  methods: {
    startDownloadFreeGeoIp() {
      this.showFreeDownload = true;
      this.showPiwikNotManagingInfo = false;
      this.progressFreeDownload = 0; // start download of free dbs

      this.downloadNextChunk(
        'downloadFreeDBIPLiteDB',
        (v) => {
          this.progressFreeDownload = v;
        },
        false,
        {},
      ).then(() => {
        window.location.reload();
      }).catch((e) => {
        this.geoipDatabaseInstalled = true;
        this.downloadErrorMessage = e.message;
      });
    },
    startAutomaticUpdateGeoIp() {
      this.buttonUpdateSaveText = translate('General_Continue');
      this.showGeoIpUpdateInfo();
    },
    showGeoIpUpdateInfo() {
      this.geoipDatabaseInstalled = true; // todo we need to replace this the proper way eventually
    },
    saveGeoIpLinks() {
      let currentDownloading: string|null = null;

      AjaxHelper.post<UpdateGeoIpLinksResponse>(
        {
          period: this.updatePeriod,
          module: 'GeoIp2',
          action: 'updateGeoIPLinks',
        },
        {
          loc_db: this.locationDbUrl,
          isp_db: this.ispDbUrl,
          org_db: this.orgDbUrl,
        },
        {
          withTokenInUrl: true,
        },
      ).then((response) => {
        if (response?.to_download) {
          const continuing = currentDownloading === response.to_download;
          currentDownloading = response.to_download; // show progress bar w/ message

          this.progressUpdateDownload = 0;
          this.progressUpdateLabel = response.to_download_label!;
          this.isUpdatingGeoIpDatabase = true; // start/continue download

          return this.downloadNextChunk(
            'downloadMissingGeoIpDb',
            (v) => {
              this.progressUpdateDownload = v;
            },
            continuing,
            {
              key: response.to_download,
            },
          );
        }

        this.progressUpdateLabel = '';
        this.isUpdatingGeoIpDatabase = false;

        NotificationsStore.show({
          message: translate('General_Done'),
          placeat: '#done-updating-updater',
          context: 'success',
          noclear: true,
          type: 'toast',
          style: {
            display: 'inline-block',
          },
          id: 'userCountryGeoIpUpdate',
        });

        this.nextRunTimePrettyUpdated = response.nextRunTime;
        $(this.$refs.inlineHelpNode as HTMLElement).effect('highlight', {
          color: '#FFFFCB',
        }, 2000);

        return undefined;
      }).catch((e) => {
        this.isUpdatingGeoIpDatabase = false;

        NotificationsStore.show({
          message: e.message,
          placeat: '#geoipdb-update-info-error',
          context: 'error',
          style: {
            display: 'inline-block',
          },
          id: 'userCountryGeoIpUpdate',
          type: 'transient',
        });
      });
    },
    downloadNextChunk(
      action: string,
      progressBarSet: (value: number) => void,
      cont: boolean,
      extraData: QueryParameters,
    ): Promise<void> {
      const data: QueryParameters = { ...extraData };

      return AjaxHelper.post<DownloadChunkResponse>(
        {
          module: 'GeoIp2',
          action,
          continue: cont ? 1 : 0,
        },
        data,
        { withTokenInUrl: true },
      ).then((response) => {
        // update progress bar
        const newProgressVal = Math.floor(
          (response.current_size / response.expected_file_size) * 100,
        );

        // if incomplete, download next chunk, otherwise, show updater manager
        progressBarSet(Math.min(newProgressVal, 100));

        if (newProgressVal < 100) {
          return this.downloadNextChunk(action, progressBarSet, true, extraData);
        }

        return undefined;
      }).catch(() => {
        throw new Error(translate('GeoIp2_FatalErrorDuringDownload'));
      });
    },
  },
  computed: {
    nextRunTimeText() {
      if (this.nextRunTimePrettyUpdated) {
        return this.nextRunTimePrettyUpdated;
      }

      if (!this.nextRunTime) {
        return translate('GeoIp2_UpdaterIsNotScheduledToRun');
      }

      if (this.nextRunTime < Date.now()) { // TODO: check it works for UTC?
        return translate('GeoIp2_UpdaterScheduledForNextRun');
      }

      return translate(
        'GeoIp2_UpdaterWillRunNext',
        `<strong>${this.nextRunTimePretty}</strong>`,
      );
    },
    providerPluginHelp() {
      if (this.isProviderPluginActive) {
        return undefined;
      }

      const text = translate('GeoIp2_ISPRequiresProviderPlugin');
      return `<div class='alert alert-warning'>${text}</div>`;
    },
    contentTitle() {
      return translate(
        this.geoipDatabaseInstalled ? 'GeoIp2_SetupAutomaticUpdatesOfGeoIP' : 'GeoIp2_GeoIPDatabases',
      );
    },
  },
});
</script>

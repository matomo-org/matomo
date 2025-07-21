<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-form class="anonymizeSettings">
    <template v-if="idSiteSpecific">
      <Field
        uicontrol="radio"
        :name="`useSiteSpecificSettings-${idSiteSpecific}`"
        :title="translate('PrivacyManager_SiteAnonymizationConfig')"
        v-model="actualUseSiteSpecificSettings"
        :options="useSiteSpecificSettingsOptions"
        :inline-help="useSiteSpecificSettingsHelpText"
      >
      </Field>
    </template>
    <template v-if="showSettings">
      <div>
        <Field
          uicontrol="checkbox"
          name="anonymizeIpSettings"
          :title="translate('PrivacyManager_UseAnonymizeIp')"
          v-model="actualEnabled"
          :inline-help="anonymizeIpEnabledHelp"
        >
        </Field>
      </div>
      <div v-show="actualEnabled">
        <div>
          <Field
            uicontrol="radio"
            name="maskLength"
            :title="translate('PrivacyManager_AnonymizeIpMaskLengtDescription')"
            v-model="actualMaskLength"
            :options="maskLengthOptions"
            :inline-help="translate('PrivacyManager_GeolocationAnonymizeIpNote')"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="radio"
            name="useAnonymizedIpForVisitEnrichment"
            :title="translate('PrivacyManager_UseAnonymizedIpForVisitEnrichment')"
            v-model="actualUseAnonymizedIpForVisitEnrichment"
            :options="useAnonymizedIpForVisitEnrichmentOptions"
            :inline-help="translate('PrivacyManager_UseAnonymizedIpForVisitEnrichmentNote')"
          >
          </Field>
        </div>
      </div>
      <div>
        <Field
          uicontrol="checkbox"
          name="anonymizeUserId"
          :title="translate('PrivacyManager_PseudonymizeUserId')"
          v-model="actualAnonymizeUserId"
        >
          <template v-slot:inline-help>
            {{ translate('PrivacyManager_PseudonymizeUserIdNote') }}
            <br/><br/>
            <em>{{ translate('PrivacyManager_PseudonymizeUserIdNote2') }}</em>
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="checkbox"
          name="anonymizeOrderId"
          :title="translate('PrivacyManager_UseAnonymizeOrderId')"
          v-model="actualAnonymizeOrderId"
          :inline-help="translate('PrivacyManager_AnonymizeOrderIdNote')"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="checkbox"
          name="forceCookielessTracking"
          :title="translate('PrivacyManager_ForceCookielessTracking')"
          v-model="actualForceCookielessTracking"
        >
          <template v-slot:inline-help>
            {{ translate('PrivacyManager_ForceCookielessTrackingDescription', trackerFileName) }}
            <br/><br/><em>{{ translate('PrivacyManager_ForceCookielessTrackingDescription2') }}</em>
            <span v-if="!trackerWritable">
              <br /><br />
              <p class='alert-warning alert'>
                {{ translate(
                  'PrivacyManager_ForceCookielessTrackingDescriptionNotWritable',
                  trackerFileName,
                ) }}
              </p>
            </span>
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="select"
          name="anonymizeReferrer"
          :title="translate('PrivacyManager_AnonymizeReferrer')"
          v-model="actualAnonymizeReferrer"
          :options="referrerAnonymizationOptions"
          :inline-help="translate('PrivacyManager_AnonymizeReferrerNote')"
        >
        </Field>
      </div>
      <div>
        <Field
          v-if="configRandomisationFeatureFlag"
          uicontrol="checkbox"
          name="randomizeConfigId"
          :title="translate('PrivacyManager_UseRandomizeConfigId')"
          v-model="actualRandomizeConfigId"
          :inline-help="translate('PrivacyManager_RandomizeConfigIdNote')"
        >
        </Field>
      </div>
    </template>
    <div class="footer-buttons">
      <SaveButton
        @confirm="save()"
        :saving="isLoading"
      />
      <button v-if="idSiteSpecific"
        class="btn btn-link"
        @click="$emit('cancel')"
      >
        {{ translate('General_Cancel', '', '') }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, AjaxHelper, NotificationsStore } from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';

interface AnonymizeIpState {
  isLoading: boolean;
  actualEnabled: boolean;
  actualUseSiteSpecificSettings: string;
  actualMaskLength: number;
  actualUseAnonymizedIpForVisitEnrichment: number;
  actualAnonymizeUserId: boolean;
  actualAnonymizeOrderId: boolean;
  actualForceCookielessTracking: boolean;
  actualAnonymizeReferrer?: string;
  actualRandomizeConfigId: boolean;
}

function boolToInt(value?: string|number|boolean): number {
  return value === true || value === 1 || value === '1' ? 1 : 0;
}

const SYSTEM_SETTINGS = 'system';
const SITE_SPECIFIC_SETTINGS = 'site-specific';

export default defineComponent({
  props: {
    ipAnonymizerEnabled: Boolean,
    anonymizeUserId: Boolean,
    ipAddressMaskLength: {
      type: [Number, String],
      required: true,
    },
    useAnonymizedIpForVisitEnrichment: {
      type: [Boolean, String, Number],
      default: 0,
    },
    anonymizeOrderId: Boolean,
    forceCookielessTracking: Boolean,
    anonymizeReferrer: String,
    maskLengthOptions: {
      type: Array,
      required: true,
    },
    useAnonymizedIpForVisitEnrichmentOptions: {
      type: Array,
      required: true,
    },
    trackerFileName: {
      type: String,
      required: true,
    },
    trackerWritable: {
      type: Boolean,
      required: true,
    },
    referrerAnonymizationOptions: {
      type: Object,
      required: true,
    },
    randomizeConfigId: Boolean,
    configRandomisationFeatureFlag: Boolean,
    idSiteSpecific: {
      type: [String, Number],
    },
    useSiteSpecificSettings: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  emits: ['updated', 'cancel'],
  data(): AnonymizeIpState {
    return {
      isLoading: false,
      actualEnabled: this.ipAnonymizerEnabled,
      actualUseSiteSpecificSettings: this.getActualUseSiteSpecificSettings(),
      actualMaskLength: +this.ipAddressMaskLength,
      actualUseAnonymizedIpForVisitEnrichment: boolToInt(
        this.useAnonymizedIpForVisitEnrichment,
      ),
      actualAnonymizeUserId: !!this.anonymizeUserId,
      actualAnonymizeOrderId: !!this.anonymizeOrderId,
      actualForceCookielessTracking: !!this.forceCookielessTracking,
      actualAnonymizeReferrer: this.anonymizeReferrer,
      actualRandomizeConfigId: !!this.randomizeConfigId,
    };
  },
  methods: {
    save() {
      this.isLoading = true;
      AjaxHelper.post(
        {
          module: 'API',
          method: 'PrivacyManager.setAnonymisationSettings',
        },
        {
          enableIpAnonymizer: boolToInt(this.actualEnabled),
          anonymizeUserId: boolToInt(this.actualAnonymizeUserId),
          anonymizeOrderId: boolToInt(this.actualAnonymizeOrderId),
          forceCookielessTracking: boolToInt(this.actualForceCookielessTracking),
          anonymizeReferrer: this.actualAnonymizeReferrer ? this.actualAnonymizeReferrer : '',
          ipAddressMaskLength: this.actualMaskLength,
          useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment,
          randomizeConfigId: boolToInt(this.actualRandomizeConfigId),
          idSiteSpecific: this.idSiteSpecific ? this.idSiteSpecific : undefined,
          useSiteSpecificSettings: this.idSiteSpecific
            ? boolToInt(this.isSiteSpecificSettingsEnabled)
            : undefined,
        },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'toast',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
        this.$emit('updated');
      }).finally(() => {
        this.isLoading = false;
      });
    },
    getActualUseSiteSpecificSettings(): string {
      console.log('getActualUseSiteSpecificSettings', this.idSiteSpecific, this.useSiteSpecificSettings);
      return (this.idSiteSpecific && this.useSiteSpecificSettings)
        ? SITE_SPECIFIC_SETTINGS
        : SYSTEM_SETTINGS;
    },
  },
  computed: {
    anonymizeIpEnabledHelp() {
      const inlineHelp1 = translate('PrivacyManager_AnonymizeIpInlineHelp');
      const inlineHelp2 = translate('PrivacyManager_AnonymizeIpDescription');
      return `${inlineHelp1} ${inlineHelp2}`;
    },
    useSiteSpecificSettingsHelpText(): string {
      return translate(
        'PrivacyManager_UseSiteSpecificSettingsHelpText',
        translate('PrivacyManager_UseSiteSpecificSettings'),
      );
    },
    showSettings(): boolean {
      return !this.idSiteSpecific || this.isSiteSpecificSettingsEnabled;
    },
    isSiteSpecificSettingsEnabled(): boolean {
      return (
        this.idSiteSpecific
        && (this.actualUseSiteSpecificSettings === SITE_SPECIFIC_SETTINGS)
      ) as boolean;
    },
    useSiteSpecificSettingsOptions() {
      return [
        {
          value: translate('PrivacyManager_UseSystemSettings'),
          key: SYSTEM_SETTINGS,
        },
        {
          value: translate('PrivacyManager_UseSiteSpecificSettings'),
          key: SITE_SPECIFIC_SETTINGS,
        },
      ];
    },
  },
});
</script>

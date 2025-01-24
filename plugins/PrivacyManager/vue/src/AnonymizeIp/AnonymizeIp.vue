<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-form>
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
    <SaveButton
      @confirm="save()"
      :saving="isLoading"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, AjaxHelper, NotificationsStore } from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';

interface AnonymizeIpState {
  isLoading: boolean;
  actualEnabled: boolean;
  actualMaskLength: number;
  actualUseAnonymizedIpForVisitEnrichment: number;
  actualAnonymizeUserId: boolean;
  actualAnonymizeOrderId: boolean;
  actualForceCookielessTracking: boolean;
  actualAnonymizeReferrer?: string;
  actualRandomizeConfigId: boolean;
}

function configBoolToInt(value?: string|number|boolean): number {
  return value === true || value === 1 || value === '1' ? 1 : 0;
}

export default defineComponent({
  props: {
    anonymizeIpEnabled: Boolean,
    anonymizeUserId: Boolean,
    maskLength: {
      type: Number,
      required: true,
    },
    useAnonymizedIpForVisitEnrichment: [Boolean, String, Number],
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
  },
  components: {
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  data(): AnonymizeIpState {
    return {
      isLoading: false,
      actualEnabled: this.anonymizeIpEnabled,
      actualMaskLength: this.maskLength,
      actualUseAnonymizedIpForVisitEnrichment: configBoolToInt(
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
          method: 'PrivacyManager.setAnonymizeIpSettings',
        },
        {
          anonymizeIPEnable: this.actualEnabled ? '1' : '0',
          anonymizeUserId: this.actualAnonymizeUserId ? '1' : '0',
          anonymizeOrderId: this.actualAnonymizeOrderId ? '1' : '0',
          forceCookielessTracking: this.actualForceCookielessTracking ? '1' : '0',
          anonymizeReferrer: this.actualAnonymizeReferrer ? this.actualAnonymizeReferrer : '',
          maskLength: this.actualMaskLength,
          useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment,
          randomizeConfigId: this.actualRandomizeConfigId ? '1' : '0',
        },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'toast',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    },
  },
  computed: {
    anonymizeIpEnabledHelp() {
      const inlineHelp1 = translate('PrivacyManager_AnonymizeIpInlineHelp');
      const inlineHelp2 = translate('PrivacyManager_AnonymizeIpDescription');
      return `${inlineHelp1} ${inlineHelp2}`;
    },
  },
});
</script>

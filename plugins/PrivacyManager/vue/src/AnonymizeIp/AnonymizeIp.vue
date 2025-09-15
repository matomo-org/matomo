<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-form class="anonymizeSettings">
    <div class="anonymizeIpSettingsField">
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
      <div class="maskLengthField">
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
      <div class="useAnonymizedIpForVisitEnrichmentField">
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
    <div class="anonymizeUserIdField">
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
    <div class="anonymizeOrderIdField">
      <Field
        uicontrol="checkbox"
        name="anonymizeOrderId"
        :title="translate('PrivacyManager_UseAnonymizeOrderId')"
        v-model="actualAnonymizeOrderId"
        :inline-help="translate('PrivacyManager_AnonymizeOrderIdNote')"
      >
      </Field>
    </div>
    <div class="forceCookielessTrackingField">
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
    <div class="anonymizeReferrerField">
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
    <div class="randomizeConfigIdField">
      <Field
        uicontrol="checkbox"
        name="randomizeConfigId"
        :title="translate('PrivacyManager_UseRandomizeConfigId')"
        v-model="actualRandomizeConfigId"
        :inline-help="randomiseConfigIdHelpText"
      >
      </Field>
    </div>
    <SaveButton
      @confirm="shouldSave()"
      :saving="isLoading"
    />
    <PasswordConfirmation
      v-model="showPasswordConfirmation"
      @confirmed="save"
    >
      <h2>{{ translate('PrivacyManager_ConfirmConfigRandomisationEnabled') }}</h2>
      <p>{{ translate('PrivacyManager_ConfirmConfigRandomisationExplanation') }}</p>
    </PasswordConfirmation>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, AjaxHelper, NotificationsStore } from 'CoreHome';
import {
  Form,
  Field,
  PasswordConfirmation,
  SaveButton,
} from 'CorePluginsAdmin';

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
  showPasswordConfirmation: boolean;
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
  },
  components: {
    Field,
    PasswordConfirmation,
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
      showPasswordConfirmation: false,
    };
  },
  methods: {
    shouldSave() {
      if (this.actualRandomizeConfigId) {
        this.showPasswordConfirmation = true;
      } else {
        this.save();
      }
    },
    save(password?: string) {
      this.isLoading = true;

      const postParams: QueryParameters = {
        anonymizeIPEnable: this.actualEnabled ? '1' : '0',
        anonymizeUserId: this.actualAnonymizeUserId ? '1' : '0',
        anonymizeOrderId: this.actualAnonymizeOrderId ? '1' : '0',
        forceCookielessTracking: this.actualForceCookielessTracking ? '1' : '0',
        anonymizeReferrer: this.actualAnonymizeReferrer ? this.actualAnonymizeReferrer : '',
        maskLength: this.actualMaskLength,
        useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment,
        randomizeConfigId: this.actualRandomizeConfigId ? '1' : '0',
      };

      if (password) {
        postParams.passwordConfirmation = password;
      }

      AjaxHelper.post(
        {
          module: 'API',
          method: 'PrivacyManager.setAnonymizeIpSettings',
        },
        postParams,
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
    randomiseConfigIdHelpText() {
      const helpText = translate('PrivacyManager_RandomizeConfigIdNote');
      const helpTextWarning = translate(
        'PrivacyManager_RandomizeConfigIdNoteWarning',
        '<strong>',
        '</strong>',
      );

      return `${helpText}<br><br>${helpTextWarning}`;
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

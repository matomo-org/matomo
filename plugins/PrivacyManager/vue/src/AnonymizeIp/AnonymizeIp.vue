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
        :name="`useSiteSpecificSettings${idSiteSpecific}`"
        :title="translate('PrivacyManager_SiteAnonymizationConfig')"
        v-model="actualUseSiteSpecificSettings"
        :options="useSiteSpecificSettingsOptions"
        :inline-help="useSiteSpecificSettingsHelpText"
      >
      </Field>
    </template>
    <template v-if="showSettings">
      <div class="anonymizeIpSettingsField">
        <Field
          uicontrol="checkbox"
          :name="`anonymizeIpSettings${idSiteSpecific}`"
          :title="translate('PrivacyManager_UseAnonymizeIp')"
          v-model="actualEnabled"
          :inline-help="anonymizeIpEnabledHelp"
          :extra-metadata="getExtraMetadataForField('ipAnonymizerEnabled')"
        >
        </Field>
      </div>
      <div v-show="actualEnabled">
        <div class="maskLengthField">
          <Field
            uicontrol="radio"
            :name="`maskLength${idSiteSpecific}`"
            :title="translate('PrivacyManager_AnonymizeIpMaskLengtDescription')"
            v-model="actualMaskLength"
            :options="maskLengthOptions"
            :inline-help="translate('PrivacyManager_GeolocationAnonymizeIpNote')"
            :extra-metadata="getExtraMetadataForField('ipAddressMaskLength')"
          >
          </Field>
        </div>
        <div class="useAnonymizedIpForVisitEnrichmentField">
          <Field
            uicontrol="radio"
            :name="`useAnonymizedIpForVisitEnrichment${idSiteSpecific}`"
            :title="translate('PrivacyManager_UseAnonymizedIpForVisitEnrichment')"
            v-model="actualUseAnonymizedIpForVisitEnrichment"
            :options="useAnonymizedIpForVisitEnrichmentOptions"
            :inline-help="translate('PrivacyManager_UseAnonymizedIpForVisitEnrichmentNote')"
            :extra-metadata="getExtraMetadataForField('useAnonymizedIpForVisitEnrichment')"
          >
          </Field>
        </div>
      </div>
      <div class="anonymizeUserIdField">
        <Field
          uicontrol="checkbox"
          :name="`anonymizeUserId${idSiteSpecific}`"
          :title="translate('PrivacyManager_PseudonymizeUserId')"
          v-model="actualAnonymizeUserId"
          :extra-metadata="getExtraMetadataForField('anonymizeUserId')"
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
          :name="`anonymizeOrderId${idSiteSpecific}`"
          :title="translate('PrivacyManager_UseAnonymizeOrderId')"
          v-model="actualAnonymizeOrderId"
          :inline-help="translate('PrivacyManager_AnonymizeOrderIdNote')"
          :extra-metadata="getExtraMetadataForField('anonymizeOrderId')"
        >
        </Field>
      </div>
      <div v-if="!idSiteSpecific" class="forceCookielessTrackingField">
        <Field
          uicontrol="checkbox"
          name="forceCookielessTracking"
          :title="translate('PrivacyManager_ForceCookielessTracking')"
          v-model="actualForceCookielessTracking"
          :extra-metadata="getExtraMetadataForField('forceCookielessTracking')"
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
          :name="`anonymizeReferrer${idSiteSpecific}`"
          :title="translate('PrivacyManager_AnonymizeReferrer')"
          v-model="actualAnonymizeReferrer"
          :options="referrerAnonymizationOptions"
          :inline-help="translate('PrivacyManager_AnonymizeReferrerNote')"
          :extra-metadata="getExtraMetadataForField('anonymizeReferrer')"
        >
        </Field>
      </div>
      <div class="randomizeConfigIdField">
        <Field
          uicontrol="checkbox"
          :name="`randomizeConfigId${idSiteSpecific}`"
          :title="translate('PrivacyManager_UseRandomizeConfigId')"
          v-model="actualRandomizeConfigId"
          :inline-help="randomiseConfigIdHelpText"
          :extra-metadata="getExtraMetadataForField('randomizeConfigId')"
        >
        </Field>
      </div>
    </template>
    <div class="footer-buttons" v-if="!idSiteSpecific">
      <SaveButton
        @confirm="shouldSave()"
        :saving="isLoading"
      />
    </div>
    <PasswordConfirmation
      v-model="showPasswordConfirmation"
      @confirmed="save"
      @aborted="abortPasswordConfirmation"
    >
      <h2>{{ passwordConfirmationTitle }}</h2>
      <p>{{ translate('PrivacyManager_ConfirmConfigRandomisationExplanation') }}</p>
    </PasswordConfirmation>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  MatomoUrl,
  NotificationsStore,
} from 'CoreHome';
import {
  Form,
  Field,
  PasswordConfirmation,
  SaveButton,
} from 'CorePluginsAdmin';

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
  showPasswordConfirmation: boolean;
}

type TMaybeObject = Record<string, unknown> | undefined;

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
    idSiteSpecific: {
      type: [String, Number],
    },
    useSiteSpecificSettings: {
      type: Boolean,
      default: false,
    },
    triggerSave: {
      type: Boolean,
      default: false,
    },
    extraMetadata: {
      type: Object,
      default: () => ({}),
    },
  },
  components: {
    Field,
    PasswordConfirmation,
    SaveButton,
  },
  directives: {
    Form,
  },
  emits: ['updated', 'aborted'],
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
      showPasswordConfirmation: false,
    };
  },
  methods: {
    shouldSave() {
      if (this.showSettings && this.actualRandomizeConfigId) {
        this.showPasswordConfirmation = true;
      } else {
        this.save();
      }
    },
    abortPasswordConfirmation() {
      this.$emit('aborted');
    },
    save(password?: string) {
      this.isLoading = true;

      const postParams: QueryParameters = {
        anonymizeIPEnable: boolToInt(this.actualEnabled),
        anonymizeUserId: boolToInt(this.actualAnonymizeUserId),
        anonymizeOrderId: boolToInt(this.actualAnonymizeOrderId),
        forceCookielessTracking: this.idSiteSpecific
          ? undefined
          : boolToInt(this.actualForceCookielessTracking),
        anonymizeReferrer: this.actualAnonymizeReferrer ? this.actualAnonymizeReferrer : '',
        ipAddressMaskLength: this.actualMaskLength,
        useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment,
        randomizeConfigId: boolToInt(this.actualRandomizeConfigId),
        idSiteSpecific: this.idSiteSpecific ? this.idSiteSpecific : undefined,
        useSiteSpecificSettings: this.idSiteSpecific
          ? boolToInt(this.isSiteSpecificSettingsEnabled)
          : undefined,
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
        if (!this.idSiteSpecific) {
          const notificationInstanceId = NotificationsStore.show({
            message: translate('CoreAdminHome_SettingsSaveSuccess'),
            context: 'success',
            id: 'privacyManagerSettings',
            type: 'toast',
          });
          NotificationsStore.scrollToNotification(notificationInstanceId);
        }
        this.$emit('updated');
      }).catch(() => {
        this.$emit('aborted');
      }).finally(() => {
        this.isLoading = false;
      });
    },
    getActualUseSiteSpecificSettings(): string {
      return (this.idSiteSpecific && this.useSiteSpecificSettings)
        ? SITE_SPECIFIC_SETTINGS
        : SYSTEM_SETTINGS;
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
    getExtraMetadataForField(fieldName: string): TMaybeObject {
      return this.extraMetadata?.[fieldName];
    },
  },
  computed: {
    anonymizeIpEnabledHelp() {
      const inlineHelp1 = translate('PrivacyManager_AnonymizeIpInlineHelp');
      const inlineHelp2 = translate('PrivacyManager_AnonymizeIpDescription');
      return `${inlineHelp1} ${inlineHelp2}`;
    },
    passwordConfirmationTitle() {
      if (this.idSiteSpecific) {
        return translate('PrivacyManager_ConfirmConfigRandomisationEnabledPerSite');
      }
      return translate('PrivacyManager_ConfirmConfigRandomisationEnabled');
    },
    useSiteSpecificSettingsHelpText(): string {
      const link = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'PrivacyManager',
        action: 'privacySettings',
      })}`;
      return translate(
        'PrivacyManager_UseSiteSpecificSettingsHelpText',
        `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
        '</a>',
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
  watch: {
    triggerSave(newValue) {
      if (newValue) {
        this.shouldSave();
      }
    },
  },
});
</script>

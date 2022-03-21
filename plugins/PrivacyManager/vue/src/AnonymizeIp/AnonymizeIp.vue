<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- REMOVE DUPLICATE CODE IN TEMPLATE
- test in UI
- check uses:
  ./plugins/PrivacyManager/templates/privacySettings.twig
  ./plugins/PrivacyManager/angularjs/anonymize-ip/anonymize-ip.controller.js
- create PR
</todo>

<template>
  <div v-form>
    <div>
      <Field
        uicontrol="checkbox"
        name="anonymizeIpSettings"
        :title="translate('PrivacyManager_UseAnonymizeIp')"
        v-model="actualEnabled"
        :inline-help="`${translate('PrivacyManager_AnonymizeIpInlineHelp')} ${translate('PrivacyManager_AnonymizeIpDescription')}`"
      >
      </Field>
    </div>
    <div v-show="enabled">
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
        :inline-help="`${translate('PrivacyManager_PseudonymizeUserIdNote')}<br/><br/><em>${translate('PrivacyManager_PseudonymizeUserIdNote2')}</em>`"
      >
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
        :inline-help="translate('PrivacyManager_ForceCookielessTrackingDescription', trackerFileName) + '<br/><br/><em>' + translate('PrivacyManager_ForceCookielessTrackingDescription2')</em>
                            {%- if not trackerWritable %}
                                  <br /><br /><p class='alert-warning alert'>translate('PrivacyManager_ForceCookielessTrackingDescriptionNotWritable', trackerFileName)</p>
                            {% endif -%}"
      >
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
  actualEnabled: unknown; // TODO
  actualMaskLength: unknown; // TODO
  actualUseAnonymizedIpForVisitEnrichment: unknown; // TODO
  actualAnonymizeUserId: unknown; // TODO
  actualAnonymizeOrderId: unknown; // TODO
  actualForceCookielessTracking: unknown; // TODO
  actualAnonymizeReferrer: unknown; // TODO
}

export default defineComponent({
  props: {
    anonymizeIP: {
      type: null, // TODO
      required: true,
    },
    maskLengthOptions: {
      type: null, // TODO
      required: true,
    },
    useAnonymizedIpForVisitEnrichmentOptions: {
      type: null, // TODO
      required: true,
    },
    trackerFileName: {
      type: null, // TODO
      required: true,
    },
    trackerWritable: {
      type: null, // TODO
      required: true,
    },
    referrerAnonymizationOptions: {
      type: null, // TODO
      required: true,
    },
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
      actualEnabled: this.enabled,
      actualMaskLength: this.maskLength,
      actualUseAnonymizedIpForVisitEnrichment: this.useAnonymizedIpForVisitEnrichment ? 1 : 0,
      actualAnonymizeUserId: this.anonymizeUserId,
      actualAnonymizeOrderId: this.anonymizeOrderId,
      actualForceCookielessTracking: this.forceCookielessTracking,
      actualAnonymizeReferrer: this.anonymizeReferrer,
    };
  },
  methods: {
    // TODO
    save() {
      this.isLoading = true;
      AjaxHelper.post({
        module: 'API',
        method: 'PrivacyManager.setAnonymizeIpSettings'
      }, {
        anonymizeIPEnable: this.enabled ? '1' : '0',
        anonymizeUserId: this.anonymizeUserId ? '1' : '0',
        anonymizeOrderId: this.anonymizeOrderId ? '1' : '0',
        forceCookielessTracking: this.forceCookielessTracking ? '1' : '0',
        anonymizeReferrer: this.anonymizeReferrer ? this.anonymizeReferrer : '',
        maskLength: this.maskLength,
        useAnonymizedIpForVisitEnrichment: parseInt(this.useAnonymizedIpForVisitEnrichment, 10) ? '1' : '0'
      }).then((success) => {
        this.isLoading = false;

        const UI = require('piwik/UI');

        const notification = new UI.Notification();
        notification.show(translate('CoreAdminHome_SettingsSaveSuccess'), {
          context: 'success',
          id: 'privacyManagerSettings'
        });
        notification.scrollToNotification();
      }, () => {
        this.isLoading = false;
      });
    },
  },
});
</script>

<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <p>{{ translate('MobileMessaging_Settings_PhoneNumbers_Help') }}</p>
    <p v-if="isSuperUser">
      {{ translate('MobileMessaging_Settings_DelegatedPhoneNumbersOnlyUsedByYou') }}
    </p>
    <div class="row">
      <h3 class="col s12">{{ translate('MobileMessaging_Settings_PhoneNumbers_Add') }}</h3>
    </div>
    <div class="form-group row">
      <div class="col s12 m6">
        <div>
          <Field
            uicontrol="select"
            name="countryCodeSelect"
            :title="translate('MobileMessaging_Settings_SelectCountry')"
            v-model="countryCallingCode"
            :full-width="true"
            :options="countries"
          >
          </Field>
        </div>
      </div>
      <div class="col s12 m6 form-help">
        {{ translate('MobileMessaging_Settings_PhoneNumbers_CountryCode_Help') }}
      </div>
    </div>
    <div class="form-group row addPhoneNumber">
      <div class="col s12 m6">
        <div class="countryCode left">
          <span class="countryCodeSymbol">+</span>
          <div>
            <Field
              uicontrol="text"
              name="countryCallingCode"
              :title="translate('MobileMessaging_Settings_CountryCode')"
              v-model="countryCallingCode"
              :full-width="true"
              :maxlength="4"
            >
            </Field>
          </div>
        </div>
        <div class="phoneNumber left">
          <div>
            <Field
              uicontrol="text"
              name="newPhoneNumber"
              v-model="newPhoneNumber"
              :title="translate('MobileMessaging_Settings_PhoneNumber')"
              :full-width="true"
              :maxlength="80"
            >
            </Field>
          </div>
        </div>
        <div class="addNumber left valign-wrapper">
          <SaveButton
            class="valign"
            :disabled="!canAddNumber || isAddingPhonenumber"
            @confirm="addPhoneNumber()"
            :value="translate('General_Add')"
          />
        </div>
        <Alert severity="warning" id="suspiciousPhoneNumber" v-show="showSuspiciousPhoneNumber">
          {{ translate('MobileMessaging_Settings_SuspiciousPhoneNumber', '54184032') }}
        </Alert>
      </div>
      <div class="col s12 m6 form-help">
        {{ strHelpAddPhone }}
      </div>
    </div>
    <div id="ajaxErrorAddPhoneNumber" />
    <ActivityIndicator :loading="isAddingPhonenumber" />
    <div class="row" v-if="Object.keys(phoneNumbers || {}).length > 0">
      <h3 class="col s12">{{ translate('MobileMessaging_Settings_ManagePhoneNumbers') }}</h3>
    </div>
    <div
      class="form-group row"
      v-for="(validated, phoneNumber, index) in phoneNumbers || []"
      :key="index"
    >
      <div class="col s12 m6">
        <span class="phoneNumber">{{ phoneNumber }}</span>
        <input
          v-if="!validated && !isActivated[index]"
          type="text"
          class="verificationCode"
          v-model="validationCode[index]"
          :placeholder="translate('MobileMessaging_Settings_EnterActivationCode')"
          style="margin-right:3.5px"
        />
        <SaveButton
          v-if="!validated && !(isActivated[index])"
          :disabled="!validationCode[index] || isChangingPhoneNumber"
          @confirm="validateActivationCode(phoneNumber, index)"
          :value="translate('MobileMessaging_Settings_ValidatePhoneNumber')"
        />
        <SaveButton
          :disabled="isChangingPhoneNumber"
          @confirm="removePhoneNumber(phoneNumber)"
          :value="translate('General_Remove')"
          style="margin-left:3.5px"
        />
      </div>
      <div class="form-help col s12 m6" v-if="!validated && !(isActivated[index])">
        <div>
            {{ translate('MobileMessaging_Settings_VerificationCodeJustSent') }}
        </div>
        &nbsp;
      </div>
    </div>
    <div id="invalidVerificationCodeAjaxError" style="display:none"></div>
    <ActivityIndicator :loading="isChangingPhoneNumber"/>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  NotificationsStore,
  AjaxHelper,
  Matomo,
  Alert,
  ActivityIndicator,
} from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';

interface ManageMobilePhoneNumbersState {
  isAddingPhonenumber: boolean;
  isChangingPhoneNumber: boolean;
  isActivated: Record<string, boolean>;
  countryCallingCode: string;
  newPhoneNumber: string;
  validationCode: Record<string, string>;
}

export default defineComponent({
  props: {
    isSuperUser: Boolean,
    defaultCallingCode: String,
    countries: {
      type: Array,
      required: true,
    },
    strHelpAddPhone: {
      type: String,
      required: true,
    },
    phoneNumbers: Object,
  },
  components: {
    Field,
    SaveButton,
    Alert,
    ActivityIndicator,
  },
  data(): ManageMobilePhoneNumbersState {
    return {
      isAddingPhonenumber: false,
      isChangingPhoneNumber: false,
      isActivated: {},
      countryCallingCode: this.defaultCallingCode || '',
      newPhoneNumber: '',
      validationCode: {},
    };
  },
  methods: {
    validateActivationCode(phoneNumber: string, index: number) {
      if (!this.validationCode[index]) {
        return;
      }

      const verificationCode = this.validationCode[index];

      this.isChangingPhoneNumber = true;
      AjaxHelper.post(
        {
          method: 'MobileMessaging.validatePhoneNumber',
        },
        {
          phoneNumber,
          verificationCode,
        },
        {
          errorElement: '#invalidVerificationCodeAjaxError',
        },
      ).then((response) => {
        this.isChangingPhoneNumber = false;

        let notificationInstanceId;
        if (!response || !response.value) {
          const message = translate('MobileMessaging_Settings_InvalidActivationCode');
          notificationInstanceId = NotificationsStore.show({
            message,
            context: 'error',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient',
          });
        } else {
          const message = translate('MobileMessaging_Settings_PhoneActivated');
          notificationInstanceId = NotificationsStore.show({
            message,
            context: 'success',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient',
          });
          this.isActivated[index] = true;
        }

        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isChangingPhoneNumber = false;
      });
    },
    removePhoneNumber(phoneNumber: string) {
      if (!phoneNumber) {
        return;
      }

      this.isChangingPhoneNumber = true;
      AjaxHelper.post(
        {
          method: 'MobileMessaging.removePhoneNumber',
        },
        {
          phoneNumber,
        },
        {
          errorElement: '#invalidVerificationCodeAjaxError',
        },
      ).then(() => {
        this.isChangingPhoneNumber = false;
        Matomo.helper.redirect();
      }).finally(() => {
        this.isChangingPhoneNumber = false;
      });
    },
    addPhoneNumber() {
      const phoneNumber = `+${this.countryCallingCode}${this.newPhoneNumber}`;

      if (this.canAddNumber && phoneNumber.length > 1) {
        this.isAddingPhonenumber = true;
        AjaxHelper.post(
          {
            method: 'MobileMessaging.addPhoneNumber',
          },
          {
            phoneNumber,
          },
          {
            errorElement: '#ajaxErrorAddPhoneNumber',
          },
        ).then(() => {
          this.isAddingPhonenumber = false;
          Matomo.helper.redirect();
        }).finally(() => {
          this.isAddingPhonenumber = false;
        });
      }
    },
  },
  computed: {
    showSuspiciousPhoneNumber() {
      return this.newPhoneNumber.trim().lastIndexOf('0', 0) === 0;
    },
    canAddNumber() {
      return !!this.newPhoneNumber && this.newPhoneNumber !== '';
    },
  },
});
</script>

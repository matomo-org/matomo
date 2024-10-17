<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
            :disabled="!canAddNumber || isUpdatingPhoneNumbers"
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
    <div id="ajaxErrorManagePhoneNumber" ref="errorContainer"></div>
    <div id="notificationManagePhoneNumber"></div>
    <div class="row" v-if="Object.keys(phoneNumbers || {}).length > 0">
      <h3 class="col s12">{{ translate('MobileMessaging_Settings_ManagePhoneNumbers') }}</h3>
    </div>
    <ActivityIndicator :loading="isUpdatingPhoneNumbers"/>
    <div
      class="form-group row"
      v-for="(verificationData, phoneNumber, index) in phoneNumbers || []"
      :key="index"
    >
      <div class="col s12 m6">
        <span class="phoneNumber">{{ phoneNumber }}</span>
        <input
          v-if="!verificationData.verified"
          type="text"
          class="verificationCode"
          v-model="validationCode[index]"
          :placeholder="translate('MobileMessaging_Settings_EnterActivationCode')"
          style="margin-right:3.5px"
        />
        <SaveButton
          v-if="!verificationData.verified"
          :disabled="!validationCode[index] || isUpdatingPhoneNumbers"
          @confirm="validateActivationCode(phoneNumber, index)"
          :value="translate('MobileMessaging_Settings_ValidatePhoneNumber')"
        />
        <SaveButton
          :disabled="isUpdatingPhoneNumbers"
          @confirm="removePhoneNumber(phoneNumber)"
          :value="translate('General_Remove')"
          style="margin-left:3.5px"
        />
      </div>
      <div class="form-help col s12 m6" v-if="!verificationData.verified">
        <div>
            {{ translate('MobileMessaging_Settings_VerificationCodeJustSent') }}
            <a @click="resendVerificationCode(phoneNumber, index)">
              {{ translate('MobileMessaging_Settings_ResendVerification') }}
            </a>
        </div>
        &nbsp;
      </div>
    </div>
  </div>
  <div
    class="ui-confirm"
    id="confirmDeletePhoneNumber"
  >
    <h2 v-html="$sanitize(removeNumberConfirmation)"></h2>
    <input
      type="button"
      role="yes"
      :value="translate('General_Yes')"
    />
    <input
      type="button"
      role="no"
      :value="translate('General_No')"
    />
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
  isUpdatingPhoneNumbers: boolean;
  phoneNumbers: Record<string, unknown>;
  countryCallingCode: string;
  newPhoneNumber: string;
  validationCode: Record<string, string>;
  numberToRemove: string;
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
  },
  components: {
    Field,
    SaveButton,
    Alert,
    ActivityIndicator,
  },
  data(): ManageMobilePhoneNumbersState {
    return {
      isUpdatingPhoneNumbers: false,
      phoneNumbers: {},
      countryCallingCode: this.defaultCallingCode || '',
      newPhoneNumber: '',
      validationCode: {},
      numberToRemove: '',
    };
  },
  mounted() {
    this.updatePhoneNumbers();
  },
  methods: {
    validateActivationCode(phoneNumber: string, index: number) {
      if (!this.validationCode[index]) {
        return;
      }

      const verificationCode = this.validationCode[index];

      this.isUpdatingPhoneNumbers = true;
      this.clearNotifcationsAndErrorsContainer();
      AjaxHelper.post(
        {
          method: 'MobileMessaging.validatePhoneNumber',
        },
        {
          phoneNumber,
          verificationCode,
        },
        {
          errorElement: '#ajaxErrorManagePhoneNumber',
        },
      ).then((response) => {
        let notificationInstanceId;
        if (!response || !response.value) {
          const message = translate('MobileMessaging_Settings_InvalidActivationCode');
          notificationInstanceId = NotificationsStore.show({
            message,
            placeat: '#notificationManagePhoneNumber',
            context: 'error',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient',
          });
        } else {
          const message = translate('MobileMessaging_Settings_PhoneActivated');
          notificationInstanceId = NotificationsStore.show({
            message,
            placeat: '#notificationManagePhoneNumber',
            context: 'success',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient',
          });
          this.updatePhoneNumbers();
        }

        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.validationCode[index] = '';
        this.isUpdatingPhoneNumbers = false;
      });
    },
    resendVerificationCode(phoneNumber: string) {
      this.isUpdatingPhoneNumbers = true;
      this.clearNotifcationsAndErrorsContainer();
      AjaxHelper.post(
        {
          method: 'MobileMessaging.resendVerificationCode',
        },
        {
          phoneNumber,
        },
        {
          errorElement: '#ajaxErrorManagePhoneNumber',
        },
      ).then(() => {
        const message = translate('MobileMessaging_Settings_NewVerificationCodeSent');
        const notificationInstanceId = NotificationsStore.show({
          message,
          placeat: '#notificationManagePhoneNumber',
          context: 'success',
          id: 'MobileMessaging_ValidatePhoneNumber',
          type: 'transient',
        });

        NotificationsStore.scrollToNotification(notificationInstanceId);
        this.updatePhoneNumbers();
      }).finally(() => {
        this.isUpdatingPhoneNumbers = false;
      });
    },
    updatePhoneNumbers() {
      this.isUpdatingPhoneNumbers = true;
      AjaxHelper.post(
        {
          method: 'MobileMessaging.getPhoneNumbers',
        },
        {
        },
      ).then((phoneNumbers) => {
        this.phoneNumbers = phoneNumbers;
        this.isUpdatingPhoneNumbers = false;
      });
    },
    removePhoneNumber(phoneNumber: string) {
      if (!phoneNumber) {
        return;
      }

      this.numberToRemove = phoneNumber;
      this.clearNotifcationsAndErrorsContainer();

      Matomo.helper.modalConfirm(
        '#confirmDeletePhoneNumber',
        {
          yes: () => {
            this.isUpdatingPhoneNumbers = true;
            AjaxHelper.post(
              {
                method: 'MobileMessaging.removePhoneNumber',
              },
              {
                phoneNumber,
              },
              {
                errorElement: '#ajaxErrorManagePhoneNumber',
              },
            ).then(() => {
              this.updatePhoneNumbers();
            }).finally(() => {
              this.isUpdatingPhoneNumbers = false;
              this.numberToRemove = '';
            });
          },
        },
      );
    },
    addPhoneNumber() {
      const phoneNumber = `+${this.countryCallingCode}${this.newPhoneNumber}`;

      if (this.canAddNumber && phoneNumber.length > 1) {
        this.isUpdatingPhoneNumbers = true;
        this.clearNotifcationsAndErrorsContainer();
        AjaxHelper.post(
          {
            method: 'MobileMessaging.addPhoneNumber',
          },
          {
            phoneNumber,
          },
          {
            errorElement: '#ajaxErrorManagePhoneNumber',
          },
        ).then(() => {
          this.updatePhoneNumbers();
          this.countryCallingCode = '';
          this.newPhoneNumber = '';
        }).finally(() => {
          this.isUpdatingPhoneNumbers = false;
        });
      }
    },
    clearNotifcationsAndErrorsContainer() {
      (this.$refs.errorContainer as HTMLElement).innerHTML = '';
      NotificationsStore.remove('MobileMessaging_ValidatePhoneNumber');
    },
  },
  computed: {
    showSuspiciousPhoneNumber() {
      return this.newPhoneNumber.trim().lastIndexOf('0', 0) === 0;
    },
    canAddNumber() {
      return !!this.newPhoneNumber && this.newPhoneNumber !== '';
    },
    removeNumberConfirmation() {
      return translate('MobileMessaging_ConfirmRemovePhoneNumber', this.numberToRemove);
    },
  },
});
</script>

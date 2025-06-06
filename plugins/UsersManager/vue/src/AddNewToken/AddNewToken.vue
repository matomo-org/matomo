<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="translate('UsersManager_AuthTokens')">
    <p>
      {{ translate('UsersManager_TokenAuthIntro') }}
    </p>

    <br v-if="noDescription || invalidExpireDate"/>
    <div class="alert alert-danger" v-if="noDescription">
      {{ translate('General_Description') }}:
      {{ translate('General_ValidatorErrorEmptyValue') }}
    </div>
    <div class="alert alert-danger" v-if="invalidExpireDate">
      {{ translate('UsersManager_TokenExpireDate') }}:
      {{ translate('UsersManager_InvalidTokenExpireDateFormat') }}
    </div>

    <form
      :action="addNewTokenFormUrl"
      method="post"
      class="addTokenForm"
    >
      <Field
        uicontrol="text"
        name="description"
        :title="translate('General_Description')"
        :maxlength="100"
        :required="true"
        :inline-help="translate('UsersManager_AuthTokenPurpose')"
        v-model="tokenDescription"
        autofocus
      />

      <Field
        uicontrol="checkbox"
        name="secure_only"
        :title="translate('UsersManager_OnlyAllowSecureRequests')"
        :required="false"
        :inline-help=secureOnlyHelp
        v-model="tokenSecureOnly"
        :disabled=forceSecureOnlyCalc
      />

      <section style="margin-bottom: 2rem">
        <h3>{{ translate('UsersManager_ExpireDate') }}</h3>

        <Field
          uicontrol="checkbox"
          name="has_expiration"
          :title="translate('UsersManager_TokenExpireDateCheckboxLabel')"
          :required="false"
          :inline-help=tokenExpireDateCheckboxHelpText
          v-model="tokenHasExpiration"
        />

        <div class="form-group row tokenExpireDateTime" v-show="tokenHasExpiration">
          <div class="col s12 m6">
            <label
              for="token_expire_date"
              class="active"
            >{{ translate('UsersManager_TokenExpireDate') }}</label>
            <input
              type="text"
              id="token_expire_date"
              name="token_expire_date"
              :value="tokenExpireDate"
              :required="tokenHasExpiration"
              @change="onKeydownTokenExpireDate($event)"
              @keydown="onKeydownTokenExpireDate($event)"
            />
          </div>
          <div class="col s12 m6 ">
            <div class="form-help">
              <span class="inline-help">
                <span>
                  <span v-html="$sanitize(tokenExpireDateHelpText)"/>
                </span>
              </span>
            </div>
          </div>
        </div>
      </section>

      <input type="hidden" :value="formNonce" name="nonce">

      <input
        type="submit"
        :value="translate('UsersManager_CreateNewToken')"
        class="btn"
        style="margin-right: 4px"
      />

      <span v-html="$sanitize(cancelLink)"></span>
    </form>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  ContentBlock,
  MatomoUrl,
  Matomo,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import ChangeEvent = JQuery.ChangeEvent;

interface AddNewTokenState {
  tokenDescription: string;
  tokenSecureOnly: boolean;
  tokenHasExpiration: boolean;
  tokenExpireDate: string|null;
  isSaving: boolean;
}

const { $ } = window;

export default defineComponent({
  props: {
    formNonce: String,
    noDescription: Boolean,
    invalidExpireDate: Boolean,
    forceSecureOnly: Boolean,
    defaultExpirationDays: Number,
    expirationReminderDays: Number,
    initialExpireDate: String,
  },
  components: {
    ContentBlock,
    Field,
  },
  data(): AddNewTokenState {
    return {
      tokenDescription: '',
      tokenSecureOnly: true,
      tokenHasExpiration: true,
      tokenExpireDate: null,
      isSaving: false,
    };
  },
  mounted() {
    this.setInitialTokenExpirationDate();
  },
  computed: {
    addNewTokenFormUrl() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'addNewToken',
      })}`;
    },
    cancelLink() {
      const backlink = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'userSecurity',
      })}`;

      return translate(
        'General_OrCancel',
        `<a class='entityCancelLink' href='${backlink}'>`,
        '</a>',
      );
    },
    forceSecureOnlyCalc() {
      return this.forceSecureOnly;
    },
    secureOnlyHelp() {
      return (this.forceSecureOnly ? translate('UsersManager_AuthTokenSecureOnlyHelpForced')
        : translate('UsersManager_AuthTokenSecureOnlyHelp'));
    },
    tokenExpireDateHelpText() {
      return translate(
        'UsersManager_TokenExpireDateHelpText',
        this.defaultExpirationDays as unknown as string,
        this.expirationReminderDays as unknown as string,
      );
    },
    tokenExpireDateCheckboxHelpText() {
      return translate(
        'UsersManager_TokenExpireDateCheckboxHelp',
        this.expirationReminderDays as unknown as string,
      );
    },
  },
  methods: {
    setInitialTokenExpirationDate() {
      const initialDate = new Date(this.initialExpireDate as string);
      const tokenExpireDateOptions = Matomo.getBaseDatePickerOptions(initialDate);
      const dtInput = $('[name="token_expire_date"]', this.$refs.root as HTMLElement);

      setTimeout(() => {
        this.tokenExpireDate = this.initialExpireDate as string;
        dtInput.datepicker(tokenExpireDateOptions);
        dtInput.datepicker('setDate', initialDate);
      });
    },
    onKeydownTokenExpireDate(event: Event|ChangeEvent) {
      setTimeout(() => {
        this.tokenExpireDate = (event.target as HTMLInputElement).value;
      });
    },
  },
});
</script>
